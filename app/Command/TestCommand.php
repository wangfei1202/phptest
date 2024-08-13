<?php

declare(strict_types=1);

namespace App\Command;

use App\Amqp\Producer\TestProducer;
use App\Lib\OSS\OssService;
use App\Lib\Platform\Walmart\Base;
use App\Lib\Utils\RequestClient;
use App\Log\LoggerToFile;
use App\Model\WalmartAdvt;
use App\Services\Deliver\DeliverPackageService;
use App\Traits\ConsoleTrait;
use App\Traits\ResponseTrait;
use Hyperf\Amqp\Producer;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Hyperf\DbConnection\Db;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Hyperf\Di\Annotation\Inject;
use Symfony\Component\Console\Input\InputOption;
use App\Amqp\Consumer\FilterVaryProductConsumer;
use App\Services\OutManage\OutManageService;

/**
 * @Command
 */
class TestCommand extends HyperfCommand
{
    use ConsoleTrait, ResponseTrait;

    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @Inject
     * @var LoggerToFile
     */
    private $logger;

    /**
     * @Inject
     * @var OssService
     */
    private $ossService;

    /**
     * @Inject
     * @var RequestClient
     */
    private $requestClient;


    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('testCommand');
    }

    public function configure()
    {
        parent::configure();
        $this->addOption('act', 'act', InputOption::VALUE_REQUIRED, 'act');
        $this->addOption('title', 'title', InputOption::VALUE_OPTIONAL, 'title');
        $this->addOption('num', 'num', InputOption::VALUE_OPTIONAL, 'num');
        $this->setDescription('testCommand');
    }

    public function handle()
    {
        try {
            $this->printNotice('开始处理');
            $act = $this->input->getOption('act') ?? '';
            if (empty($act)) {
                throw new \Exception('参数act不能为空');
            }
            if (!method_exists($this, $act)) {
                throw new \Exception('method not exists');
            }
            $result = $this->$act();
            if ($result['code'] != 200) {
                throw new \Exception($result['message'], (int)$result['code']);
            }
            $this->printNotice('处理完成');
        }catch (\Exception $e) {
            $msg = sprintf('程序异常 code: %s message: %s', $e->getCode(), $e->getMessage());
            $this->printError($msg);
        }
    }

    //php bin/hyperf.php testCommand --act cronTask
    public function cronTask(){
        $packageConfig = Db::table('package_config')
            ->where('status',1)
            ->where('id',66)
            ->get()
            ->toArray();
        foreach ($packageConfig as $config){
            $dateConfig = json_decode($config['condition'],true);
            if(empty($dateConfig)){
                continue;
            }
            //按照配置的json生成时间规则
            foreach ($dateConfig as $k => $dateItem){
                $currentDayOfWeek = date('N'); // 获取当前是周几，1表示周一，2表示周二，以此类推
                $currentTime = date('H:i:00'); // 获取当前时间，格式为小时:分钟:秒
                //计算当前配置的星期几
                $configDayOfWeek = $k + 1;
                $exclude = array("00:00:00");
                foreach ($dateItem['date'] as &$date){
                    $date = $date.":00";
                }
                $configDate = array_diff($dateItem['date'],$exclude);
                if (true) {
                    $config['config_type'] = 2;
                    make(DeliverPackageService::class)->generateTask($config);
                }
            }
        }
        return $this->success('success');
    }

    //php bin/hyperf.php testCommand --act test
    public function test(){
        var_dump(config('open_api_url'));
        return $this->success('success');
    }
}
