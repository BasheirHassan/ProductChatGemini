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
            $this->load->language('catalog/product');
            $url_route = $this->url->link('extension/product_chat_gemini/event/product_chat_gemini|get_data_from_gemini', 'user_token=' . $this->session->data['user_token'], false);


            $data['input_description'] = $this->config->get($this->module . "_" . "input_description");
            $data['input_meta_keyword'] = $this->config->get($this->module . "_" . "input_meta_keyword");
            $data['input_meta_description'] = $this->config->get($this->module . "_" . "input_meta_description");
            $data['input_meta_title'] = $this->config->get($this->module . "_" . "input_meta_title");
            $data['input_tag'] = $this->config->get($this->module . "_" . "input_tag");

            $model_config = json_encode($data);




            $html = '<link rel="stylesheet" href="../extension/product_chat_gemini/admin/view/javascript/loading/loading.css">' . PHP_EOL;
            $html .= '<script src="../extension/product_chat_gemini/admin/view/javascript/loading/jquery.loading.min.js"></script>' . PHP_EOL;
            $html .= '<script src="../extension/product_chat_gemini/admin/view/javascript/loading/gemini.js"></script>' . PHP_EOL;
            $html .= "<script type='text/javascript'> 
              $(document).ready(function () {
                    loadGeminiStatus('$json_languages','$model_config','$url_route');
              });
             </script>" . PHP_EOL;


            $find = $args['footer'];
            $output = str_replace($find, $html . $find, $output);


        }
    }



    public function get_data_from_gemini(): void
    {
        // Get the API key, and retrieve the user's prompt
        $key = $this->config->get('module_product_chat_gemini_api_key');
        $status = false;
        $message ="تم جلب البيانات بنجاح";

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

            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=$key";

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
                $message ="لم يتم جلب البيانات بنجاح" . $err;
            } else {
                $status=true;
                // Parse the JSON response
                $data = json_decode($response, true);
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? ( $status= false);
                if (!$status){
                    $message ="لم يتم جلب البيانات بنجاح";
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
