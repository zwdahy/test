<?php

class TestPagelet extends BasePagelet {

    public function run($params){
        $this->setView('hello', 'hello');
        $this->setView('arg1', $params['arg1']);
        $this->display('pagelets/test.tpl');
    }

}
