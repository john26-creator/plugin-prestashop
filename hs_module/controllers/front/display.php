<?php

class Hs_moduledisplayModuleFrontController extends ModuleFrontControllerCore
{
    public function initContent()
    {
        parent ::initContent();
        $this -> setTemplate('module:hs_module/views/templates/front/display.tpl');
    }
}