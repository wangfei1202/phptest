<?php
function add($a,$b){
    return $a+$b;
}

echo json_encode(['code'=>1,'msg'=>'','data'=>['result'=>add(1,2)]]);



