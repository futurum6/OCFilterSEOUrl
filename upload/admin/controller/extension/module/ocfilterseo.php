<?php
class ControllerExtensionModuleOcfilterseo extends Controller
{
    private $error = [];

    /**
     * Main method to handle the module's index page.
     */
    public function index(): void   
    {
        $this->load->language('extension/module/ocfilterseo');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');

        if ($this->isPostRequest() && $this->validate()) {
     
            $this->model_setting_setting->editSetting('ocfilterseo', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
           // $this->response->redirect($this->getExtensionLink());
        }

        $data = $this->getTemplateData();
        $this->response->setOutput($this->load->view('extension/module/ocfilterseo', $data));
    }

    /**
     * Check if the current request is a POST request.
     *
     * @return bool
     */
    private function isPostRequest(): bool  
    {
        return $this->request->server['REQUEST_METHOD'] == 'POST';
    }

    /**
     * Generate the URL link for the extension.
     *
     * @return string
     */
    private function getExtensionLink(): string   
    {
        return $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
    }

    /**
     * Prepare data for the template.
     *
     * @return array
     */
    private function getTemplateData(): array   
    {
        return [
            'error_warning' => $this->error['warning'] ?? '',
            'success' => $this->session->data['success'] ?? '',
            'ocfilterseo_status' => $this->request->post['ocfilterseo_status'] ?? $this->config->get('ocfilterseo_status'),
            'ocfilterseo_max_filters_per_block' => $this->request->post['ocfilterseo_max_filters_per_block'] ?? $this->config->get('ocfilterseo_max_filters_per_block'),
            'ocfilterseo_max_link_blocks' => $this->request->post['ocfilterseo_max_link_blocks'] ?? $this->config->get('ocfilterseo_max_link_blocks'),
            'action' => $this->url->link('extension/module/ocfilterseo', 'user_token=' . $this->session->data['user_token'], true),
            'cancel' => $this->getExtensionLink(),
            'header' => $this->load->controller('common/header'),
            'column_left' => $this->load->controller('common/column_left'),
            'footer' => $this->load->controller('common/footer')
        ];
    }

    /**
     * Validate user permissions.
     *
     * @return bool
     */
    protected function validate(): bool 
    {
        if (!$this->user->hasPermission('modify', 'extension/module/ocfilterseo')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        return !$this->error;
    }
}
