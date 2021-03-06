<?php
class ControllerPaymentMandarinbank extends Controller {
    private $error = array();

    public function index() {
        $this->language->load('payment/mandarinbank');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('mandarinbank', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
        }

        $this->data['heading_title'] = $this->language->get('heading_title');

        $this->data['text_enabled'] = $this->language->get('text_enabled');
        $this->data['text_disabled'] = $this->language->get('text_disabled');
        $this->data['text_all_zones'] = $this->language->get('text_all_zones');
        $this->data['text_pay'] = $this->language->get('text_pay');
        $this->data['text_card'] = $this->language->get('text_card');

        $this->data['entry_merchant'] = $this->language->get('entry_merchant');
        $this->data['entry_signature'] = $this->language->get('entry_signature');
        $this->data['entry_type'] = $this->language->get('entry_type');
        $this->data['entry_total'] = $this->language->get('entry_total');
        $this->data['entry_order_status'] = $this->language->get('entry_order_status');
        $this->data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
        $this->data['entry_status'] = $this->language->get('entry_status');
        $this->data['entry_sort_order'] = $this->language->get('entry_sort_order');

        $this->data['button_save'] = $this->language->get('button_save');
        $this->data['button_cancel'] = $this->language->get('button_cancel');

        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

        if (isset($this->error['merchant'])) {
            $this->data['error_merchant'] = $this->error['merchant'];
        } else {
            $this->data['error_merchant'] = '';
        }

        if (isset($this->error['signature'])) {
            $this->data['error_signature'] = $this->error['signature'];
        } else {
            $this->data['error_signature'] = '';
        }

        if (isset($this->error['type'])) {
            $this->data['error_type'] = $this->error['type'];
        } else {
            $this->data['error_type'] = '';
        }

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_payment'),
            'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('payment/mandarinbank', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->data['action'] = $this->url->link('payment/mandarinbank', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

        if (isset($this->request->post['mandarinbank_merchant'])) {
            $this->data['mandarinbank_merchant'] = $this->request->post['mandarinbank_merchant'];
        } else {
            $this->data['mandarinbank_merchant'] = $this->config->get('mandarinbank_merchant');
        }

        if (isset($this->request->post['mandarinbank_signature'])) {
            $this->data['mandarinbank_signature'] = $this->request->post['mandarinbank_signature'];
        } else {
            $this->data['mandarinbank_signature'] = $this->config->get('mandarinbank_signature');
        }

        if (isset($this->request->post['mandarinbank_type'])) {
            $this->data['mandarinbank_type'] = $this->request->post['mandarinbank_type'];
        } else {
            $this->data['mandarinbank_type'] = $this->config->get('mandarinbank_type');
        }

        if (isset($this->request->post['mandarinbank_total'])) {
            $this->data['mandarinbank_total'] = $this->request->post['mandarinbank_total'];
        } else {
            $this->data['mandarinbank_total'] = $this->config->get('mandarinbank_total');
        }

        if (isset($this->request->post['mandarinbank_order_status_id'])) {
            $this->data['mandarinbank_order_status_id'] = $this->request->post['mandarinbank_order_status_id'];
        } else {
            $this->data['mandarinbank_order_status_id'] = $this->config->get('mandarinbank_order_status_id');
        }

        $this->load->model('localisation/order_status');

        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        if (isset($this->request->post['mandarinbank_geo_zone_id'])) {
            $this->data['mandarinbank_geo_zone_id'] = $this->request->post['mandarinbank_geo_zone_id'];
        } else {
            $this->data['mandarinbank_geo_zone_id'] = $this->config->get('mandarinbank_geo_zone_id');
        }

        $this->load->model('localisation/geo_zone');

        $this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        if (isset($this->request->post['mandarinbank_status'])) {
            $this->data['mandarinbank_status'] = $this->request->post['mandarinbank_status'];
        } else {
            $this->data['mandarinbank_status'] = $this->config->get('mandarinbank_status');
        }

        if (isset($this->request->post['mandarinbank_sort_order'])) {
            $this->data['mandarinbank_sort_order'] = $this->request->post['mandarinbank_sort_order'];
        } else {
            $this->data['mandarinbank_sort_order'] = $this->config->get('mandarinbank_sort_order');
        }

        $this->template = 'payment/mandarinbank.tpl';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'payment/mandarinbank')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['mandarinbank_merchant']) {
            $this->error['merchant'] = $this->language->get('error_merchant');
        }

        if (!$this->request->post['mandarinbank_signature']) {
            $this->error['signature'] = $this->language->get('error_signature');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }
}
?>