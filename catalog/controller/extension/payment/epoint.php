<?php
/**
 * @pluginName epoint Opencart 3.x Payment Gateway
 * @pluginUrl https://epoint.az/
 * @varion 1.0.0
 * @author Rauf ABBASZADE <rafo.abbas@gmail.com>
 * @authorURI: https://abbasazade.dev/
 * @opencartVersion "3.0.x"
 */

class ControllerExtensionPaymentEpoint extends Controller
{
    /**
     * Epoint class
     * @var array
     */
    public $epoint;

    /**
     * Order
     * @var array
     */
    public $order;

    public function index()
    {
        $data['button_confirm'] = $this->language->get('button_confirm');

        $this->load->model('checkout/order');
        if (!isset($this->session->data['order_id'])) {
            return false;
        }
        return $this->load->view('extension/payment/epoint', $data);
    }

    public function registerEpoint()
    {
        require_once DIR_SYSTEM . 'library/epoint.class.php';

        $this->epoint = new Epoint();

        $this
            ->epoint
            ->setPublicKey($this->config->get('payment_epoint_public_key'))
            ->setPrivateKey($this->config->get('payment_epoint_private_key'));
    }


    public function confirm()
    {
        $this->registerEpoint();


        $json = [];

        if (isset($this->session->data['payment_method']['code']) && $this->session->data['payment_method']['code'] == 'epoint') {

            $this->load->model('checkout/order');

            $this->order = $this->model_checkout_order->getOrder($this->session->data['order_id']);

            $orderId = $this->order['order_id'];

            $response = $this->epoint->request('1/request', $this->epoint->payload([
                'public_key' => $this->config->get('payment_epoint_public_key'),
                'amount' => (float)$this->order['total'],
                'currency' => $this->config->get('payment_epoint_currency'),
                'language' => 'az',
                'order_id' => $this->order['order_id'],
                'description' => 'Order payment: ' . str_pad($orderId, 7, "0", STR_PAD_LEFT),
                "success_redirect_url" => $this->url->link('extension/payment/epoint/callback') . "&order_id=" . $orderId,
                "error_redirect_url" => $this->url->link('extension/payment/epoint/callback') . "&order_id=" . $orderId
            ]));

            $json = json_decode($response, true);

            $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('config_order_status_id'));

            $this->load->model('extension/payment/epoint');

            $this
                ->model_extension_payment_epoint
                ->insertLog($orderId,
                    $json['transaction'],
                    $response
                );

        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function callback()
    {
        $this->registerEpoint();

        if ($this->request->server['REQUEST_METHOD'] == 'GET' and isset($this->request->get['order_id'])) {

            $this->load->model('extension/payment/epoint');

            $payment_detail = $this->model_extension_payment_epoint->getLog($this->request->get['order_id']);

            if ($payment_detail) {

                $response = $this->epoint->request('1/get-status', $this->epoint->payload([
                    'public_key' => $this->config->get('payment_epoint_public_key'),
                    'transaction' => $payment_detail['payment_id'],
                ]));

                $json = json_decode($response, true);

                if (isset($json['status']) and $json['status'] == 'success') {
                    $this->load->model('checkout/order');
                    $this->model_checkout_order->addOrderHistory($this->request->get['order_id'], $this->config->get('payment_epoint_order_status_id'), '', true);
                    $this->response->redirect($this->url->link('checkout/success'));
                } else {
                    $this->response->redirect($this->url->link('checkout/failure'));
                }
            }else {
                $this->response->redirect($this->url->link('checkout/failure'));
            }
        }else {
            $this->response->redirect($this->url->link('checkout/failure'));
        }
    }
}