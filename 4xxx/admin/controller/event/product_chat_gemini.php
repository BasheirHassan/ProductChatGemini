<?php


namespace Opencart\Admin\Controller\Extension\ProductChatGemini\Event;
class ProductChatGemini extends \Opencart\System\Engine\Controller
{

    private $path = 'extension/product_chat_gemini/module/product_chat_gemini';
    private $module = 'module_product_chat_gemini';

    public function init(&$route, &$args, &$output): void
    {


        $this->load->model('localisation/language');
        $languages = $this->model_localisation_language->getLanguages();
        $json_languages = json_encode($languages);






        if ($this->config->get($this->module . '_status')) {

           $this->load->language($this->path);


            $lang_help_setting_extension =  $this->language->get('help_setting_extension');
            $lang_btn_run_all =  $this->language->get('btn_run_all');
            $lang_help_model_use =  $this->language->get('help_model_use');


            $this->load->language('catalog/product');
            $url_route = $this->url->link('extension/product_chat_gemini/event/product_chat_gemini|get_data_from_gemini', 'user_token=' . $this->session->data['user_token'], false);
            $url_extension = $this->url->link($this->path, 'user_token=' . $this->session->data['user_token'], false);


            $data['input_description'] = $this->config->get($this->module . "_" . "input_description");
            $data['input_meta_keyword'] = $this->config->get($this->module . "_" . "input_meta_keyword");
            $data['input_meta_description'] = $this->config->get($this->module . "_" . "input_meta_description");
            $data['input_meta_title'] = $this->config->get($this->module . "_" . "input_meta_title");
            $data['input_tag'] = $this->config->get($this->module . "_" . "input_tag");
            $data['select_model'] = $this->config->get($this->module . "_" . "select_model");
            $data['select_model_name'] = $this->config->get($this->module . "_" . "select_model_name");

            $model_config = json_encode($data);




            $html = '<link rel="stylesheet" href="' . HTTP_CATALOG . '/extension/product_chat_gemini/admin/view/css/loading.css">' . PHP_EOL;
            $html .= '<script src="' . HTTP_CATALOG . '/extension/product_chat_gemini/admin/view/javascript/jquery.loading.min.js"></script>' . PHP_EOL;
            $html .= '<script src="' . HTTP_CATALOG . '/extension/product_chat_gemini/admin/view/javascript/gemini.js"></script>' . PHP_EOL;
            $html .= "<script type='text/javascript'> 
              $(document).ready(function () {
                    loadGeminiStatus('$json_languages','$model_config','$url_route');
              });
             </script>" . PHP_EOL;


            $find = $args['footer'];
            $output = str_replace($find, $html . $find, $output);




            $find  ='<button type="submit" form="form-product" data-bs-toggle="tooltip" title="'.$args['button_save'].'" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i></button>';
            $html  ='<a href="'.$url_extension.'" data-bs-toggle="tooltip" class="btn btn-danger" aria-label="Back" data-bs-original-title="'.$lang_help_setting_extension.'"><i class="fa-solid fa fa-cogs"></i></a>';
            $html .='<button type="button" onclick="getGeminiAll()" data-bs-toggle="tooltip" title="'.$lang_btn_run_all .$lang_help_model_use.'['. $data['select_model_name'].']" class="btn btn-primary m-1"><i class="fa-solid fa-bookmark"></i></button>';
            $output = str_replace($find, $html . $find, $output);

            $find ='<button type="submit" form="form-category" data-bs-toggle="tooltip" title="'.$args['button_save'].'" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i></button>';
            $html ='<button type="button" onclick="getGeminiAll()" data-bs-toggle="tooltip" title="'.$lang_btn_run_all.'" class="btn btn-primary m-1"><i class="fa-solid fa-bookmark"></i></button>';
            $output = str_replace($find, $html . $find, $output);





        }
    }



    public function get_data_from_gemini(): void
    {


        // Get the API key, and retrieve the user's prompt
        $this->load->language($this->path);
        $key = $this->config->get('module_product_chat_gemini_api_key');
        $select_model = $this->config->get('module_product_chat_gemini_select_model');

        $status = false;
        $text = "";
        $message = $this->language->get('response_ok');


        if ($this->config->get('module_product_chat_gemini_api_key') && isset($this->request->post['gemini_content'])) {
            $prompt = $this->request->post['gemini_content'];

            // Encode the prompt in JSON, and create the request
            $json_data = json_encode([
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => $prompt
                            ]
                        ]
                    ]
                ]
            ]);
             $url = "https://generativelanguage.googleapis.com/v1beta/$select_model:generateContent?key=$key";


            // Send the request, and get the response
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            curl_close($ch);



            if(curl_errno($ch)) {
                $err = 'Request Error: ' . curl_error($ch);
                $status =false;
                $message =$this->language->get('response_ok') . $err .$response;
            } else {
                $status=true;
                // Parse the JSON response
                $data = json_decode($response, true);
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? ( $status= false);
                if (!$status){
                    $message =$this->language->get('response_error') .PHP_EOL. $data['error']['message'];
                }

            }
// Close the cURL session
            curl_close($ch);
        }

        echo json_encode([
            'status' => $status,
            'message' => $message,
            'result' => $text
        ]);

    }

}
