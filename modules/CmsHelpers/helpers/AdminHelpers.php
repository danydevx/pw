<?php namespace ProcessWire;

class AdminHelpers extends Process {

      public function renderView($view,$params){
        return $this->files->render($view,$params); 
      }

     

     public function createBreadcrumbs($breads= []){
        $this->wire('breadcrumbs')->removeAll();
        $breadcrumbs = new Breadcrumbs(); 
            if(count($breads)>0){
                foreach ($breads as $b){
                    $breadcrumbs->add(new Breadcrumb($b['url'], $b['title'])); 
                }

           }
        $this->wire('breadcrumbs', $breadcrumbs);  
      }

     


      public function createTitlePage($pageTitle=''){
         $this->wire('processBrowserTitle',$pageTitle);
         $this->process->headline($pageTitle);
      }

      public function createThumbnail($image='',$width=100,$height=100){
                  $img='';
                if($image) {
                  $thumb = $image->size($width,$height);
                  $img = "<img src='$thumb->url'>";
                }else{
                    $img ='<img src="//via.placeholder.com/'.$width.'x'.$height.'" alt="">';
                }
                return $img;
      }


         public function showErrors($error =  'You dont have permission to this action'){
                            $this->error($error , Notice::log);
                            $this->error($error ." (admin)", Notice::superuser);
                            return false;
                            
         }
         

         public function getActionsNavbar($params){

                 $output='';
                 $output.=$this->files->render($params['viewPath'].'navbar.view.php',$params);
                 return $output;


         }




   public function createButtonDelete($btn){
                    $button='';
                    $button.='<a href="' . $btn['href']. '" title=""   class="uk-button uk-button-primary uk-button-small ui-corner-all mx-2  uk-button-danger delete-item"  >'.$btn['label'].'</a>';
                    return $button;
               }


                public function createButtonEditModal($btn){
                     $button='';
                    $button.='<a href="' . $btn['href']. '" title=""  data-barba-prevent="" data-buttons="button.ui-button[type=submit]" data-autoclose="" data-reload="true" class="uk-button uk-button-primary uk-button-small ui-corner-all mx-2 pw-modal '.$btn['css'].'"  >'.$btn['label'].'</a>';
                    return $button;
                }


                public function createButtonEdit($btn){
                    $button='';
                    $button.='<a href="' . $btn['href']. '" title=""  data-barba-prevent="" data-buttons="button.ui-button[type=submit]" data-autoclose="" data-reload="true" class="uk-button uk-button-primary uk-button-small ui-corner-all mx-2 '.$btn['css'].'"  >'.$btn['label'].'</a>';
                    return $button;
                }

                public function createButtonEditPanel($btn,$width='90%'){
                   
                    $button='';
                    $button.='<a href="' . $btn['href']. '" title="'.$btn['label'].'"  data-panel-width="'.$width.'" data-tab-text="Edit" data-tab-offset="200" data-tab-icon="close" data-reload="true" data-barba-prevent="" data-buttons="button.ui-button[type=submit]" data-autoclose="" class="pw-panel pw-panel-right uk-button-primary   pw-panel-reload uk-button uk-button-small ui-corner-all mx-2 "  '.$btn['css'].'>'.$btn['label'].'</a>';
                    return $button;

                }


                 public function createButtonView($btn){
                    $button='';
                    $button.='<a href="' . $btn['href']. '" title=""  data-barba-prevent="" data-buttons="button.ui-button[type=submit]" data-autoclose="" data-reload="true" class="uk-button uk-button-primary uk-button-small ui-corner-all mx-2 '.$btn['css'].'"  >'.$btn['label'].'</a>';
                    return $button;
                }


                 public function createButtonViewModal($btn){
                     $button='';
                    $button.='<a href="' . $btn['href']. '" title=""  data-barba-prevent="" data-buttons="button.ui-button[type=submit]" data-autoclose="" data-reload="true" class="uk-button uk-button-primary uk-button-small ui-corner-all mx-2 pw-modal '.$btn['css'].'"  >'.$btn['label'].'</a>';
                    return $button;
                }


                 public function createButtonViewPanel($btn,$width='90%'){
                   
                    $button='';
                    $button.='<a href="' . $btn['href']. '" title="'.$btn['label'].'"  data-panel-width="'.$width.'" data-tab-text="Edit" data-tab-offset="200" data-tab-icon="close" data-reload="true" data-barba-prevent="" data-buttons="button.ui-button[type=submit]" data-autoclose="" class="pw-panel pw-panel-right uk-button-primary   pw-panel-reload uk-button uk-button-small ui-corner-all mx-2 "  '.$btn['css'].'>'.$btn['label'].'</a>';
                    return $button;

                }


                
       public function renderViewField($item, $name = "", $size = 100, $fieldView = '',  $newLabel = '', $element = '') {
          $output = '';
          
       
          $fieldSize = !empty($size) ? $size : 100;
          $fieldName = !empty($name) ? $name : '';
          $fieldLabel = !empty($newLabel) ? $newLabel : $item->fields->{$name}->label;
          $fieldContainer = !empty($element) ? $element : 'div';
          // Verifica si fieldView es true y asigna el valor correspondiente a fieldValue
          $fieldValue = !empty($fieldView) ? $item->renderField($name,$fieldView) : $item->renderField($name);
         
          $output .= '<'.$fieldContainer.' class="field-view field-' . $fieldName . '" style="width: ' . $fieldSize . '%;" data-colwidth="' . $fieldSize . '%;">
                  <div class="field-content">
                      <h5 class="field-label"><strong>' . $fieldLabel . '</strong></h5>
                      <div class="field-value">' . $fieldValue . '</div>
                  </div>
              </'.$fieldContainer.'>';

          return $output;
      }



 

 
 

}