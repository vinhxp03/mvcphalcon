<?php

namespace Multiple\Frontend\Controllers;

use Phalcon\Mvc\Controller;

class ProductsController extends Controller
{
    public function indexAction()
    {
    	// echo $this->url->get('login');die();
        //return $this->response->redirect($this->url->get('login'));
        return $this->response->redirect($this->url->get('login'));
        //return $this->response->redirect('admin/products/index');
    }
}
