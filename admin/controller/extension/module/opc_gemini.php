<?php

class ControllerExtensionModuleOpcGemini extends Controller
{
    private $error = array();

    public function index()
    {



        $data = $this->load->language('extension/module/opc_gemini');

        if ($this->request->post) {
            $this->request->post = array_map('trim', $this->request->post);
        }

        $this->document->setTitle($this->language->get('heading_title'));

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->load->model('setting/setting');

            $this->model_setting_setting->editSetting('module_opc_gemini', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

        $opc_error = array(
            'warning',
            'api_key'
        );

        foreach ($opc_error as $key => $value) {
            if (isset($this->error[$value])) {
                $data['error_' . $value] = $this->error[$value];
            } else {
                $data['error_' . $value] = '';
            }
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/opc_gemini', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['user_token'] = $this->session->data['user_token'];

        $data['user_guide'] = $this->url->link('extension/module/opc_gemini/user_guide', 'user_token=' . $this->session->data['user_token'], true);

        $data['action'] = $this->url->link('extension/module/opc_gemini', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        $opc_module_config = array(
            'status',
            'api_key'
        );

        foreach ($opc_module_config as $key => $value) {
            if (isset($this->request->post['module_opc_gemini_' . $value])) {
                $data['module_opc_gemini_' . $value] = $this->request->post['module_opc_gemini_' . $value];
            } else {
                $data['module_opc_gemini_' . $value] = $this->config->get('module_opc_gemini_' . $value);
            }
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');


        $this->response->setOutput($this->load->view('extension/module/opc_gemini', $data));
    }

    public function user_guide()
    {
        $this->document->setTitle('OpenCartCity Chat With gemini User Guide');

        $data['cancel'] = $this->url->link('extension/module/opc_gemini', 'user_token=' . $this->session->data['user_token'], true);

        $data['user_token'] = $this->session->data['user_token'];

        $data['header'] = $this->load->controller('common/header');

        $data['column_left'] = $this->load->controller('common/column_left');

        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/opc_gemini_user_guide', $data));
    }


    /**
     * اضافة عادا حالة الاضافة الى قاعدة البيانات
     * This function is used to get the status of the gemini module
     * @return void
     */
    public function get_status()
    {
        // Get the status of the gemini module from the database
        $result =  $this->config->get('module_opc_gemini_status');
        // Return the status of the gemini module
        echo json_encode(['result'=>$result]);
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/module/opc_gemini')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!isset($this->request->post['module_opc_gemini_api_key']) || !$this->request->post['module_opc_gemini_api_key']) {
            $this->error['api_key'] = $this->language->get('error_api_key');
        }

        return !$this->error;
    }

    function run_curl($url, $json_data)

    {

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);

        curl_close($ch);

        return $response;

    }


    public function getData()
    {
        $json['gemini_content_result'] = '';
        $key = $this->config->get('module_opc_gemini_api_key');
        if ($this->config->get('module_opc_gemini_api_key') && isset($this->request->post['gemini_content']) && $this->request->post['gemini_content']) {


            $prompt = $this->request->post['gemini_content'];

            $json_data = '{

      "contents": [{

        "parts":[{

          "text":"' . $prompt . '"}]}]}';
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=$key";


            $response = $this->run_curl($url, $json_data);

            echo($response);

            return $response;


        }
    }
}
