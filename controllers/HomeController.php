<?php

class HomeController
{
    public function index() 
    {
        $title = 'Trang chủ';
        require_once PATH_VIEW_MAIN;
    }
}