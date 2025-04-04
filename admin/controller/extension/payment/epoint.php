<?php
/**
 * @pluginName epoint Opencart 3.x Payment Gateway
 * @pluginUrl https://epoint.az/
 * @varion 1.0.0
 * @author Rauf ABBASZADE <rafo.abbas@gmail.com>
 * @authorURI: https://abbasazade.dev/
 * @opencartVersion "3.0.x"
 */

class ControllerExtensionPaymentEpoint extends Controller {

    /**
     * Error messages
     * @var array $error
     */
    private $error = [];

    /**
     * View data
     * @var array $data
     */
    public $data = [];

    public function index()
    {
        $this->loading();

        $this->document->setTitle($this->language->get('heading_title'));

        // update epoint settings
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->update();
        }

        $this->data();

        $this->setError();

        $this->setConfig();

        $this->data['message'] = '';

        if (isset($this->request->get['message'])) {
            $this->data['message'] = $this->request->get['message'];
        }

        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $this->response->setOutput($this->load->view('extension/payment/epoint', $this->data));
    }

    protected function loading()
    {
        $this->load->model('localisation/order_status');
        $this->load->language('extension/payment/epoint');
        $this->load->model('setting/setting');
    }

    protected function update()
    {
        if(! $this->config->get('public_key')) {

            $this->load->model('extension/payment/epoint');

            $this->model_extension_payment_epoint->generateTable();

        }
        $this->model_setting_setting->editSetting('payment_epoint', $this->request->post);

        $this->session->data['success'] = $this->language->get('text_success');

        return $this->response->redirect($this->url->link('extension/payment/epoint', 'user_token=' . $this->session->data['user_token'] . '&type=payment&message='.$this->language->get('text_success'), true));
    }

    protected function setConfig(): void
    {
        if (isset($this->request->post['payment_epoint_public_key']))
        {
            $this->data['payment_epoint_public_key'] = $this->request->post['payment_epoint_public_key'];
        } else {
            $this->data['payment_epoint_public_key'] = $this->config->get('payment_epoint_public_key');
        }

        if (isset($this->request->post['payment_epoint_private_key']))
        {
            $this->data['payment_epoint_private_key'] = $this->request->post['payment_epoint_private_key'];
        } else {
            $this->data['payment_epoint_private_key'] = $this->config->get('payment_epoint_private_key');
        }

        if (isset($this->request->post['payment_epoint_status']))
        {
            $this->data['payment_epoint_status'] = $this->request->post['payment_epoint_status'];
        } else {
            $this->data['payment_epoint_status'] = $this->config->get('payment_epoint_status');
        }

        if (isset($this->request->post['payment_epoint_sort_order']))
        {
            $this->data['payment_epoint_sort_order'] = $this->request->post['payment_epoint_sort_order'];
        } else {
            $this->data['payment_epoint_sort_order'] = $this->config->get('payment_epoint_sort_order');
        }

        if (isset($this->request->post['payment_epoint_currency'])) {
            $this->data['payment_epoint_currency'] = $this->request->post['payment_epoint_currency'];
        } else {
            $this->data['payment_epoint_currency'] = $this->config->get('payment_epoint_currency');
        }

        if (isset($this->request->post['payment_epoint_language'])) {
            $this->data['payment_epoint_language'] = $this->request->post['payment_epoint_language'];
        } else {
            $this->data['payment_epoint_language'] = $this->config->get('payment_epoint_language');
        }

        if (isset($this->request->post['payment_epoint_currency_usd_convert_azn'])) {
            $this->data['payment_epoint_currency_usd_convert_azn'] = $this->request->post['payment_epoint_currency_usd_convert_azn'];
        } else {
            $this->data['payment_epoint_currency_usd_convert_azn'] = $this->config->get('payment_epoint_currency_usd_convert_azn');
        }

        if (isset($this->request->post['payment_epoint_currency_eur_convert_azn'])) {
            $this->data['payment_epoint_currency_eur_convert_azn'] = $this->request->post['payment_epoint_currency_eur_convert_azn'];
        } else {
            $this->data['payment_epoint_currency_eur_convert_azn'] = $this->config->get('payment_epoint_currency_eur_convert_azn');
        }

        if (isset($this->request->post['payment_epoint_order_status_id'])) {
            $this->data['payment_epoint_order_status_id'] = $this->request->post['payment_epoint_order_status_id'];
        } else {
            $this->data['payment_epoint_order_status_id'] = $this->config->get('payment_epoint_order_status_id');
        }
    }

    protected function setError()
    {
        $this->data = array_merge($this->data, array(
            'error_warning' => isset($this->error['warning']) ? $this->error['warning'] : '',
            'error_language' => isset($this->error['language']) ? $this->error['language'] : '',
            'error_public_key' => isset($this->error['public_key']) ? $this->error['public_key'] : '',
            'error_private_key' => isset($this->error['private_key']) ? $this->error['private_key'] : '',
            'error_currency' => isset($this->error['currency']) ? $this->error['currency'] : '',
            'error_currency_usd_convert_azn' => isset($this->error['currency_usd_convert_azn']) ? $this->error['currency_usd_convert_azn'] : '',
            'error_currency_eur_convert_azn' => isset($this->error['currency_eur_convert_azn']) ? $this->error['currency_eur_convert_azn'] : '',
            'error_order_status_id' => isset($this->error['order_status_id']) ? $this->error['order_status_id']: ''
        ));
    }

    protected function data()
    {
        $this->data = array(
            'action' => $this->url->link('extension/payment/epoint', 'user_token=' . $this->session->data['user_token'], true),
            'cancel' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true),
            'callback' => HTTP_CATALOG . 'index.php?route=extension/payment/epoint/callback',
            'header' => $this->load->controller('common/header'),
            'column_left' => $this->load->controller('common/column_left'),
            'footer' => $this->load->controller('common/footer'),
            'breadcrumbs' => [
                [
                    'text' => $this->language->get('text_home'),
                    'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
                ],
                [
                    'text' => $this->language->get('text_extension'),
                    'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
                ],
                [
                    'text' => $this->language->get('heading_title'),
                    'href' => $this->url->link('extension/payment/epoint', 'user_token=' . $this->session->data['user_token'], true)
                ]
            ]
        );
    }

    protected function validate()
    {
        if (! $this->user->hasPermission('modify', 'extension/payment/epoint')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (empty($this->request->post['payment_epoint_order_status_id'])) {
            $this->error['order_status_id'] = $this->language->get('error_order_status_id');
        }

        if (empty($this->request->post['payment_epoint_public_key'])) {
            $this->error['public_key'] = $this->language->get('error_public_key');
        }

        if (empty($this->request->post['payment_epoint_private_key'])) {
            $this->error['private_key'] = $this->language->get('error_private_key');
        }
        if (empty($this->request->post['payment_epoint_currency'])) {
            $this->error['currency'] = $this->language->get('error_currency');
        }

        if (empty($this->request->post['payment_epoint_currency_eur_convert_azn'])) {
            $this->error['currency_eur_convert_azn'] = $this->language->get('error_currency_eur_convert_azn');
        }

        if (empty($this->request->post['payment_epoint_currency_usd_convert_azn'])) {
            $this->error['currency_usd_convert_azn'] = $this->language->get('error_currency_usd_convert_azn');
        }
        if (empty($this->request->post['payment_epoint_language'])) {
            $this->error['language'] = $this->language->get('error_language');
        }

        return empty($this->error);
    }
}