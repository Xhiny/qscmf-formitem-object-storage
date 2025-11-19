<?php

namespace FormItem\ObjectStorage\Controller;

use FormItem\ObjectStorage\Lib\Common;
use FormItem\ObjectStorage\Lib\Vendor\Context;

class ObjectStorageController extends \Think\Controller{

    public function callBack(){
        header('Access-Control-Allow-Methods: *');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');
        if (strtolower($_SERVER['REQUEST_METHOD'])==='options'){
            return;
        }

        $type = I('get.type');
        $vendor_type = Common::getVendorType($type, I('get.vendor_type'));
        $title = I("get.title");
        $title = $title ? Common::decodeTitle($title) : $title;
        $hash_id = Common::getHashId();
        $resize = I("get.resize");

        $config = C('UPLOAD_TYPE_' . strtoupper($type));
        if(!$config){
            E('获取不到文件规则config设置');
        }

        $os_cls = Context::genVendorByType($vendor_type);
        $body_arr = $os_cls->genClient($type)->extraObject(array_merge($config, ['cb_key' => I("get.key")]));
        if($body_arr === false){
            $this->ajaxReturn(array('err_msg' => '上传的文件找不到'));
        }
        if ($title && !isset($body_arr['title'])){
            $body_arr['title'] = $title;
        }

        $mime_type = $body_arr['mimeType'];
        $hash_id = $hash_id ?: Common::extraValidHashId($body_arr['hash_id']);

        if(!empty($config['mimes'])){
            $mimes = explode(',', $config['mimes']);
            if(!in_array(strtolower($mime_type), $mimes)){
                $this->ajaxReturn(array('err_msg' => '上传的文件类型不符合要求'));
            }
        }

        $file_data = $os_cls->extraFile($config, $body_arr);
        if (!isset($file_data['mime_type'])){
            $file_data['mime_type'] = $mime_type;
        }
        if ($hash_id && !isset($file_data['hash_id'])){
            $file_data['hash_id'] = $hash_id;
        }
        $file_data['cate'] = $type;
        $file_data['original'] = $file_data['title'];

        C('TOKEN_ON',false);

        $this->check($file_data, $config);

        $params = [];
        if (\Think\Hook::get('handle_os_callback')){
            if ($resize && !isset($body_arr['resize'])){
                $body_arr['resize'] = $resize;
            }
            $file_data = Common::handleCbRes($file_data, $os_cls, $body_arr['resize']);

            $params = [
                'file_data'=>$file_data,
                'param' => [...I('get.'), ...$body_arr]
            ];
            \Think\Hook::listen('handle_os_callback', $params);
        }

        if (isset($params['res']) && $params['res'] === true){
            $this->ajaxReturn($params['file_data']);
        }

        $r = D('FilePic')->createAdd($file_data);
        if($r === false){
            E(D('FilePic')->getError());
        }
        else{
            if ($resize && !isset($body_arr['resize'])){
                $body_arr['resize'] = $resize;
            }
            $file_data = Common::handleCbRes($file_data, $os_cls, $body_arr['resize']);
            $res = [
                'file_id' => $r,
                'file_url' => $file_data['url'],
                'status' => 1
            ];
            isset($file_data['small_url']) && $res['small_url'] = $file_data['small_url'];

            $this->ajaxReturn($res);
        }
    }

    private function checkSize($size, $config_max_size): bool
    {
        return !($size > $config_max_size) || (0 === $config_max_size);
    }

    private function checkExt($ext, $config_ext): bool
    {
        return empty($config_ext) || in_array(strtolower($ext), $config_ext, true);
    }

    private function check($file, $config) {
        /* 检查文件大小 */
        if (!$this->checkSize($file['size'], $config['maxSize'])) {
            $this->ajaxReturn(array('err_msg' => '上传文件大小不符！'.'(<='.floor($config['maxSize']/1024/1024).'MB)'));
        }

        /* 检查文件后缀 */
//        if (!$this->checkExt($file['ext'], $config['ext'])) {
//            $this->ajaxReturn(array('err_msg' => '上传文件后缀不允许！'));
//        }
    }

    public function policyGet($type, $vendor_type = ''){
        $hash_id = Common::getHashId();
        $get_data = I("get.");
        $resize = $get_data['resize'] ?? '';
        $title = $get_data['title'];
        $vendor_type = Common::getVendorType($type, $vendor_type);
        $os_cls = Context::genVendorByType($vendor_type);
        $os_cls && $os_cls->setUploadConfig($type);

        if ($hash_id && $file_data = Common::getFileByHash($hash_id, $os_cls, $title, $resize)){
            $res = [
                'file_id' => $file_data['id'],
                'file_url' => $file_data['url'],
                'status' => 2
            ];

            isset($file_data['small_url']) && $res['small_url'] = $file_data['small_url'];

            $this->ajaxReturn($res);
        }

        $config = C('UPLOAD_TYPE_' . strtoupper($type));
        if ($get_data['file_size'] && !$this->checkSize($get_data['file_size'], $config['maxSize'])) {
            $this->ajaxReturn(array('err_msg' => '上传文件大小不符！' . '(<=' . floor($config['maxSize'] / 1024 / 1024) . 'MB)'));
        }

        $response = $os_cls->policyGet($type);
        $response['vendor_type'] = $vendor_type;
        $response['upload_config'] = [
            'max_size' => Common::getMaxSize($type)
        ];

        $this->ajaxReturn($response);
    }

    public function download(int $file_id){
        $ent = D("FilePic")->where(['id' => $file_id])->find();
        $url = showFileUrl($file_id);
        header("Content-type: application/force-download");
        header('Content-Disposition: inline; filename="' . $ent['title'] . '"');
        header("Content-Transfer-Encoding: Binary");
        header("Content-length: " . $ent['size']);
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $ent['title'] . '"');
        echo file_get_contents($url);
    }
}
