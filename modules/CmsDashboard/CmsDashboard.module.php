<?php namespace ProcessWire;

class CmsDashboard extends Process {


 
 

    //Inicia todas las variables
    public function init()
    {
            $this->helpers =  $this->modules->get('CmsHelpers');
    

    }





    public function ___execute(){
              
         $breadcrumbs =  array(
                  array('url'=>'#',
                        'title'=>'Dashboard'),
              );
          $this->helpers->createTitlePage('Dashboard'); 
          $this->helpers->createBreadcrumbs($breadcrumbs);


          $viewParams=[];
            $output=$this->files->render(__DIR__.'/views/dashboard.view.php',$viewParams);
          return $output;
       
    }
 
 
  
}
