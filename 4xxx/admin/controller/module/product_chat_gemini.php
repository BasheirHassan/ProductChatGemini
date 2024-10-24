<?php

//AIzaSyBPQYhdE6rkdv3j6XXJX3pqen_izFXV_wU

namespace Opencart\Admin\Controller\Extension\ProductChatGemini\Module;
class ProductChatGemini extends \Opencart\System\Engine\Controller {

	private $path = 'extension/product_chat_gemini/module/product_chat_gemini';
	private $event = 'extension/product_chat_gemini/event/product_chat_gemini';
	private $module = 'module_product_chat_gemini';
    private $description = 'Add  / Product Chat Gemini';

	public function index(): void {
		$languageModel = $this->load->language($this->path);
		$this->load->model('setting/extension');
        $this->load->language('catalog/product');
		$this->document->setTitle(strip_tags($languageModel['heading_title']));



        $this->load->model('localisation/language');
        $languages = $data['languages'] = $this->model_localisation_language->getLanguages();


        $chat_module_config = array(
            'status',
            'api_key',
        );

        foreach ($languages as $key => $value) {
            array_push($chat_module_config,
                'input_description',
                'input_meta_keyword',
                'input_meta_description',
                'input_meta_title',
                'input_tag'
            );
        }


        foreach ($chat_module_config as $key => $value) {
            if (isset($this->request->post['module_product_chat_gemini_' . $value])) {
                $data['module_product_chat_gemini_' . $value] = $this->request->post['module_product_chat_gemini_' . $value];
            } else {
                $data['module_product_chat_gemini_' . $value] = $this->config->get('module_product_chat_gemini_' . $value);
            }
        }




		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module')
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->path, 'user_token=' . $this->session->data['user_token'])
		];

		$data['save'] = $this->url->link($this->path.'|save', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module');

		$data[$this->module.'_status'] = $this->config->get($this->module.'_status');
    
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->path, $data));
	}

	public function save(): void {

		$this->load->language($this->path);
		$json = [];

		if (!$this->user->hasPermission('modify', $this->path)) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
           $this->init();
			$this->load->model('setting/setting');
			$this->model_setting_setting->editSetting($this->module, $this->request->post);
			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}


	private function init(): void {
        $x = (VERSION >= '4.0.2') ? '.' : '|';
        $this->load->model('setting/event');
        $this->model_setting_event->deleteEventByCode($this->module);


        $this->model_setting_event->addEvent([
            'code'			=> $this->module,
            'description'	=> $this->description,
            'trigger'       => 'admin/view/catalog/product_form/after',
            'action'		=> $this->event.$x.'init',
            'status'		=> true,
            'sort_order'	=> 0
        ]);




        $this->model_setting_event->addEvent([
            'code'			=> $this->module,
            'description'	=> $this->description,
            'trigger'       => 'admin/view/catalog/category_form/after',
            'action'		=> $this->event.$x.'init',
            'status'		=> true,
            'sort_order'	=> 0
        ]);





        $this->load->model('user/user_group');
        $groups = $this->model_user_user_group->getUserGroups();

        foreach($groups as $group) {
            $this->model_user_user_group->addPermission($group['user_group_id'], 'access', $this->event);
        }

	}

	public function install(): void {
		if ($this->user->hasPermission('modify', $this->path)) {
			$this->init();
		}
	}

	public function uninstall(): void {
		if ($this->user->hasPermission('modify', $this->path)) {
			$this->load->model('setting/event');
			$this->model_setting_event->deleteEventByCode($this->module);
		}
	}



}