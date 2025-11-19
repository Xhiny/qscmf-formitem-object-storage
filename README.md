# qscmf-formitem-object-storage
上传文件至云服务商的一体化组件

#### 安装

```php
composer require quansitech/qscmf-formitem-object-storage
```



#### 特性

1. 支持上传至不同供应商
2. 支持文件查重，避免重复上传同一个文件



#### 替换 *oss/cos* 上传组件用法

```text
若项目之前使用了 quansitech/qscmf-formitem-aliyun-oss 或者 quansitech/qscmf-formitem-tengxun-cos 上传组件，以下是替换步骤 
```

- 移除 *oss/cos* 扩展包
- [**安装**](https://github.com/quansitech/qscmf-formitem-object-storage#%E5%AE%89%E8%A3%85)此扩展包
- 执行数据迁移
- 添加数据迁移文件，修改 *qs_file_pic* 数据表，赋值 vendor_type字段
- 按照[**使用**](https://github.com/quansitech/qscmf-formitem-object-storage#%E4%BD%BF%E7%94%A8)修改配置
- 根据[**支持组件用法**](https://github.com/quansitech/qscmf-formitem-object-storage#%E6%94%AF%E6%8C%81%E7%BB%84%E4%BB%B6)修改后台的上传组件
- 若前台使用了 *ossuploader* ，参考 [***osuploader* 用法**](https://github.com/quansitech/qscmf-formitem-object-storage#%E7%AE%80%E5%8D%95%E4%BD%BF%E7%94%A8)修改
- 若前后端开发分离的项目，前端按照[**自定义组件用法**](https://github.com/quansitech/qscmf-formitem-object-storage#%E8%87%AA%E5%AE%9A%E4%B9%89%E7%BB%84%E4%BB%B6%E7%94%A8%E6%B3%95)修改上传组件
- _think-core_ v13版本将不兼容 _ueditor oss_ 的旧配置，请参考以下文档修改



#### 使用

+ 配置使用的供应商，若同时配置，则优先级按顺序依次降低
  
  + 调用组件时设置 *options*
  
    ```text
    适用情景：项目使用了多个云服务商，且同一个上传配置类型需要上传至不同云服务商
    ```
    
    ```php
    // 使用formItem
    ->addFormItem("picture_cos", "picture_os", "封面cos","",['vendor_type' => 'tengxun_cos'])
    
    // 使用富文本则是配置第七个参数
    // data-url 根据实际入口文件修改
    ->addFormItem('oss', 'ueditor', 'oss','', '','','data-url="/extends/ueditor/index?os=1&type=ueditor&vendor_type=aliyun_oss"')
    
    // 使用columnItem
    ->addTableColumn("picture", "封面tos", 'picture_os', ['vendor_type' => 'volcengine_tos'], true)
    ```
  
  + 请求 policy 等信息接口的参数
    ```text
    接口地址默认为 /extends/objectStorage/policyGet ，参数名为 vendor_type  
    ```
  
  + 修改 *upload_config.php* 的上传配置
  
    ```text
    适用情景：项目使用了多个云服务商，同一个上传配置类型上传至固定的云服务商
    ```
    
    ```php
      'UPLOAD_TYPE_IMAGE' => array(
          // 其他配置省略
            'oss_host' => env("ALIOSS_HOST"),
            'oss_meta' => array('Cache-Control' => 'max-age=2592000'),
            'vendorType' => 'aliyun_oss',
        ),
    ```
    
  + 添加 *env* *OS_VENDOR_TYPE*，全局配置
  
    ```text
    适用情景：项目只使用一个云服务商
    ```
    
    ```php
    OS_VENDOR_TYPE=aliyun_oss
    ```
  
  
  
+ 供应商可选值及其相关配置

  | 名称           | 供应商   |
  | -------------- | -------- |
  | aliyun_oss     | 阿里云   |
  | tengxun_cos    | 腾讯云   |
  | volcengine_tos | 火山引擎 |

  - **aliyun_oss**

    - *env* 配置

      | 名称                     |
      | ------------------------ |
      | ALIOSS_ACCESS_KEY_ID|
      | ALIOSS_ACCESS_KEY_SECRET|
      | ALIOSS_HOST|
      | ALIOSS_BUCKET|
      | ALIOSS_ENDPOINT|
      | ALIOSS_REGION|
  
    - *upload_config.php* 需要添加的配置
  
      ```text
      当 upload_oss_host 为空时，上传默认使用 oss_host
      
      当 oss_host 为自定义域名时，可以将 upload_oss_host 设置为 bucket 域名，接口返回的路径会是自定义域名
      其他类型的 upload_xxx_host 是同样的作用，不再说明
      
      自定义域名常用于配置CDN加速域名
      ```

      | 名称            | 是否必填 | 备注       |
      | --------------- | -------- | ---------- |
      | oss_host        | 是       |            |
      | upload_oss_host | 否       | 上传用域名 |
    
  - **tengxun_cos**
  
    - *env* 配置
  
      | 名称          |
      | ------------- |
      | COS_SECRETID  |
      | COS_SECRETKEY |
      | COS_HOST      |
      | COS_BUCKET    |
      | COS_ENDPOINT  |
      | COS_REGION    |
  
    - *upload_config.php* 需要添加的配置
  
        | 名称            | 是否必填 | 备注       |
        | --------------- | -------- | ---------- |
        | cos_host        | 是       |            |
        | upload_cos_host | 否       | 上传用域名 |
  
  - **volcengine_tos**
  
    - *env* 配置
  
        | 名称            |
        | --------------- |
        | VOLC_ACCESS_KEY |
        | VOLC_SECRET_KEY |
        | VOLC_HOST       |
        | VOLC_BUCKET     |
        | VOLC_ENDPOINT   |
        | VOLC_REGION     |
  
    - *upload_config.php* 需要添加的配置
  
      | 名称            | 是否必填 | 备注       |
      | --------------- | -------- | ---------- |
      | tos_host        | 是       |            |
      | upload_tos_host | 否       | 上传用域名 |

  *upload_config.php* 通用配置
  
  | 名称            | 是否必填 | 备注     |
  |------|--------| ---------- |
  | os_upload_meta        | 否    | 设置 HTTP 标准头域,如 Content-Type 等 |
  
+ 配置查重功能，0 关闭，1 开启，默认为 1，若同时配置，则优先级按顺序依次降低

  + 调用组件时设置 *options*

    ```php
    // 使用formItem
    ->addFormItem("picture_cos", "picture_os", "封面cos","",['file_double_check' => 0])
    
    // 使用columnItem
    ->addTableColumn("picture", "封面tos", 'picture_os', ['file_double_check' => 0], true)
    ```
  
  + 添加 *env* *OS_FILE_DOUBLE_CHECK*，全局配置
    ```php
    OS_FILE_DOUBLE_CHECK=1
    ```

+ 添加自定义参数到回调
  + 在请求地址追加自定义参数
  + 使用钩子注入自定义参数
    
    ```php 
    // 注册钩子
    Hook::add('inject_os_params', InjectOsParamBehavior::class);
    ```
  
    ```php 
    class InjectOsParamBehavior
      {
    
          public function run(&$params)
          {
    
              // 省略具体的自定义参数
    
          }
    
    
      }
    ```

  
+ 修改上传成功逻辑

  参数说明

  | 参数          | 是否必选 | 类型    | 说明                               |
  |-------------|------|-------|----------------------------------|
  | res         | 是    | bool  | 返回 true 则需要返回修改上传逻辑并返回 file_data |
  | file_data       | 是    | array | 返回内容，一般为上传后的文件信息                 |


  ```php
  // 注册钩子
  Hook::add('handle_os_callback', HandleOsResBehavior::class);
  ```  
  
  ```php 
  // 需要添加修改标识 res 以及需要返回的内容 file_data 
    class HandleOsResBehavior
    {
        public function run(&$params)
        {
            $file_data = $params['file_data'];
            $custom_param =  $params['param'];
            if ($custom_param['scence'] === 'ueditor'){
                $data = [
                    "state" => 'SUCCESS',
                    "url" => $file_data["url"],
                    "size" => $file_data["size"],
                    "title" => htmlspecialchars($file_data["title"]),
                    "original" => htmlspecialchars($file_data["original"]),
                    "source" => $file_data
                ];
        
                $params['file_data'] = $data;
                $params['res'] = true;
            }
        
        }
    
    }
  ```
  

#### 支持组件

##### ColumnItem

- 上传文件：file_os

  ```php
  ->addTableColumn("file_id", "单个文件", 'file_os', '', true)
  ```

  

- 上传图片：picture_os

  ```php
  ->addTableColumn("picture", "封面tos", 'picture_os', '', true)
  ```

  

##### FormItem

- 上传音频：audio_os/audios_os

  ```php
  ->addFormItem('audio_id', 'audio_os', '单个音频')
  ->addFormItem('audios_id', 'audios_os', '多个音频')
  ```

  

- 上传文件：file_os/files_os

  ```php
  ->addFormItem('file_id', 'file_os', '单个文件')
  ->addFormItem('files_id', 'files_os', '多个文件')
  ```

  

- 上传图片：picture_os/pictures_os

  ```php
  ->addFormItem('picture_id', 'picture_os', '单张图片')
  ->addFormItem('pictures_id', 'pictures_os', '多张图片')
  
  $options = ['process'=>'?x-oss-process=image/resize,m_fill,w_300,h_200'] //其中w_300,h_200为参数宽300高200，请参考不同供应商图片处理参数，根据实际需求填写
  // 如表单没有显示缩略图需求，$options可以不传，$options为空时显示原图
  ->addFormItem('picture_id', 'picture_os', '单张图片', '',$options , '', '')
  ```

  

- 上传裁剪后的图片：picture_os_intercept/pictures_os_intercept

  ```php
  $option = [
    'type' => 'image', // 默认值
    'width' => 1, // 裁剪框宽高比例，此为宽度，默认为1
    'height' => 1 // 裁剪框宽高比例，此为高度，默认1
  ];
  
  // 如没有特别需求，$option可不传
  ->addFormItem('cover', 'picture_os_intercept', '单张裁剪后的图片', '', $option)
  ->addFormItem('covers', 'pictures_os_intercept', '多张裁剪后的图片')
  ```
  
- 富文本上传文件： ueditor
  ```text
  富文本使用服务器上传，其他配置参考服务器上传功能说明
  ```

  addFormItem第七个参数，传递指定的上传处理地址, 地址参数说明

  | 参数名称      | 类型     | 是否必填 | 备注                                          |
  |---------------|--------|---------------------------------------------|-------------------------------------|
  | os | string | 是    | 恒为1                                         |
  | type        | string | 否    | 与上传配置 UPLOAD_TYPE_XXX 的 XXX 对应，如富文本 ueditor |
  | vendor_type | array  | 否    | 供应商类型                                       |

  ```php
  ->addFormItem('oss', 'ueditor', 'oss','', '','','data-url="/extends/ueditor/index?os=1&type=ueditor&vendor_type=aliyun_oss"')
  ->addFormItem('tos', 'ueditor', 'tos','', '','','data-url="/extends/ueditor/index?os=1&type=ueditor&vendor_type=volcengine_tos"')
  ->addFormItem('cos', 'ueditor', 'cos','', '','','data-url="/extends/ueditor/index?os=1&type=ueditor&vendor_type=tengxun_cos"')
  ```

  配置说明
  - **aliyun_oss**

      - *upload_config.php* 需要添加的配置
        ```text
        当上传资源过大时，可以配置内网访问 endpoint，此值会替换掉 ALIOSS_ENDPOINT
        其他类型的 xxx_endpoint 是同样的作用，不再说明
        ```

        | 名称  | 是否必填 | 备注       |
        | --------------- | -------- | ---------- |
        | oss_endpoint   | 否       |  |

  - **volcengine_tos**

      - *upload_config.php* 需要添加的配置

        | 名称         | 是否必填 | 备注       |
        | --------------- | -------- | ---------- |
        | tos_endpoint | 否       |  |


#### 自定义组件用法

此扩展包使用的是直传功能，主要分为以下步骤：

1. 服务端实现签名，返回请求的路径等信息
2. 客户端发起POST请求，直传文件到云服务商。



##### 参考文档

| 名称           | 供应商                                                       |
| -------------- | ------------------------------------------------------------ |
| aliyun_oss     | [阿里云-服务端签名直传](https://help.aliyun.com/zh/oss/use-cases/obtain-signature-information-from-the-server-and-upload-data-to-oss#concept-en4-sjy-5db) |
| tengxun_cos    | [腾讯云-Web 端直传实践](https://cloud.tencent.com/document/product/436/9067) |
| volcengine_tos | 火山引擎 [PostObject](https://www.volcengine.com/docs/6349/129228) 和  [Post 表单预签名](https://www.volcengine.com/docs/6349/1123288) |



##### 用法

1. 向服务端请求 *policy* 等信息

   

   **接口地址：/extends/objectStorage/policyGet**

   

   **请求方法**

   **GET**

   

   **请求参数**

   | 参数          | 是否必选 | 类型   | 说明                                                               |
   |-------------|------|------------------------------------------------------------------|------------------------------------------------------------------|
   | type        | 是    | string | 与 upload_config.php 中 UPLOAD_TYPE_XXX 的 XXX 对应，如图片 image；文件 file |
   | vendor_type | 否    | string | 供应商名称                                                            |
   | title       | 是    | string | 文件标题                                                             |
   | hash_id     | 否    | string | 文件 MD5 信息，可用于查重                                                  |
   | file_type   | 是    | string | 文件 mime-type 类型，例如 image/png                                     |
   | jump        | 否    | string | 为0则不使用腾讯云的重定向功能，仅 vendor_type 为 tengxun_cos有效                    |

   

   **返回示例**

   ###### 正常

   - 提交了 *hash_id* 且已存在文件，则直接返回文件信息
    
      ```json
      {
        "file_id": "5657",
        "file_url": "url",
        "status": 2
      }
      ```
   
   - 返回不同供应商接口所需参数  

     - aliyun_oss

       ```json
       {
         "accessid": "testaccessid",
         "host": "host",
         "policy": "xxx",
         "expire": 1696934405,
         "callback": "xxx",
         "callback_var": "xxx",
         "dir": "Uploads/file/20231010/652529fb6bd3e",
         "vendor_type": "aliyun_oss",
         "upload_config": "xxx"
       }
       ```

     - tengxun_cos

       ```json
       {
         "host": "host",
         "url": "url",
         "authorization": "authorization",
         "params": {
           "key": "Uploads/image/20231010/652527bb43ebd.png",
           "success_action_redirect": "url"
         },
         "vendor_type": "tengxun_cos"
       }
       ```
       jump 为 0
       ```json      
       {
         "host": "host",
         "url": "url",
         "authorization": "authorization",
         "params": {
           "key": "Uploads/image/20231010/652527bb43ebd.png",
           "x-cos-return-body": "returnBody"
         },
         "vendor_type": "tengxun_cos",
         "jump_url": "url",
         "upload_config": "xxx"
       }
       ```

     - volcengine_tos

       ```json
       {
           "url": "url",
           "params": {
               "Content-Type": "image/png",
               "name": "test.png",
               "x-tos-callback": "xxx",
               "x-tos-callback-var": "xxx",
               "x-tos-credential": "testAK/20231009/cn-guangzhou/tos/request",
               "x-tos-algorithm": "TOS4-HMAC-SHA256",
               "x-tos-date": "20231009T111513Z",
               "key": "Uploads/image/20231009/6523704131c24.png",
               "policy": "xxx",
               "x-tos-signature": "xxx"
           },
           "vendor_type": "volcengine_tos",
           "upload_config": "xxx"
       }
       ```

   ###### 异常

   以接口实际返回值为准

   

2. 使用 *Post* 方法向云服务商发送文件上传请求

   - aliyun_oss

     - 请求地址为**步骤1**返回的 *host*
     - 按照官方文档组装表单字段

   - tengxun_cos

        - 请求地址为**步骤1**返回的 *url*
        - 按照官方文档组装表单字段          

   - volcengine_tos

     - 请求地址为**步骤1**返回的 *url*
     - *formData* 需与**步骤1**返回的 *params* 一致

      

   2. 返回示例

      ###### 正常

      - 上传成功
      
          ```json
          {
            "file_id": "5693",
            "file_url": "url",
            "status": 1
      	  }
         ```
      
      ###### 异常
      
      以接口实际返回值为准

#### 类使用说明

[FormItem\ObjectStorage\Lib\Common 帮助函数类使用说明](https://github.com/quansitech/qscmf-formitem-object-storage/blob/master/Common.md)

[FormItem\ObjectStorage\Lib\Context 供应商生成类使用说明](https://github.com/quansitech/qscmf-formitem-object-storage/blob/master/Context.md)

[FormItem\ObjectStorage\Lib\Vendor 供应商类使用说明](https://github.com/quansitech/qscmf-formitem-object-storage/blob/master/Vendor.md)



#### 服务端上传文件

###### 用法

```php
$file_path = WWW_DIR.'/Uploads/image/xxx/xxx.jpg';
$object = 'Uploads/image/xxx/xxx.jpg';
$options = [
    'Content-Type' => 'image/png'
];

// Context 供应商生成类用法参考上文使用说明
$os_vendor = Context::genVendorByType('volcengine_tos');
$os_vendor->setBucket("bucket");
$os_vendor->setEndpoint("bucket");

$res = $os_vendor->genClient('image')->uploadFile($file_path, $object, $options);
```

###### 配置说明
- **aliyun_oss**

    - *upload_config.php* 需要添加的配置
      ```text
      当上传资源过大时，可以配置内网访问 endpoint，此值会替换掉 ALIOSS_ENDPOINT
      其他类型的 xxx_endpoint 是同样的作用，不再说明
      ```

      | 名称  | 是否必填 | 备注       |
      | --------------- | -------- | ---------- |
      | oss_endpoint   | 否       |  |
  
- **volcengine_tos**

    - *upload_config.php* 需要添加的配置

      | 名称         | 是否必填 | 备注       |
      | --------------- | -------- | ---------- |
      | tos_endpoint | 否       |  |





## <a name="os_uploader">os_uploader 上传图片</a>

### 介绍

  图片裁剪上传功能,内置cropper.js,可配置是否裁剪。

### 功能：

* 可配置是否裁剪

* 可以定义裁剪尺寸,裁剪比例

* 可以定义限制文件后缀、大小以及是否允许选取重复文件

* 限制上传张数

* 可自定义提示函数

* 裁剪上传图会默认转成jpg;png透明背景默认转成白色
  
* 支持文件查重，避免重复上传同一个文件
  
  
  
  ##### 简单使用
  
  ```php
  // 服务端生成 url 和 供应商类型 vendor_type
  public function customUpload(){
      list($data_url, $vendor_type)= Common::genPolicyDataUrl('image');
  
      $this->assign('data_url', $data_url);
      $this->assign('vendor_type', $vendor_type);
      $this->assign('cacl_file_hash', Common::needCaclFileHash());
  
      $this->display();
  }
  ```
  
  
  
  ```javascript
  // customUpload.html
  
  <!DOCTYPE html>
  <html lang="en">
  <head>
      <meta charset="UTF-8" />
      <title>Document</title>
  </head>
  <body>
  <input type="hidden" name="test_os" value="{$form.value}" data-srcjson="{$pictures_json}">
  
  <notdefined name="pictures_os_intercept">
      // 需要同时引入静态资源
      <script src="__PUBLIC__/libs/jquery/jquery.js"></script>
  
      <link rel="stylesheet" href="{:asset('object-storage/osuploader/jquery.osuploader.css')}">
      <link rel="stylesheet" href="{:asset('object-storage/cropper/cropper.min.css')}">
  
      <script type="text/javascript" src="{:asset('object-storage/cropper/cropper.js')}"></script>
      <script type="text/javascript" src="{:asset('object-storage/plupload-2.3.9/js/plupload.full.min.js')}"></script>
      <script type="text/javascript" src="{:asset('object-storage/osuploader/jquery.osuploader.js')}"></script>
          
      // 需要按顺序引入此资源
      <script type="text/javascript" src="{:asset('object-storage/file-md5-wasm/js/md5.min.js')}"></script>
      <script type="text/javascript" src="{:asset('object-storage/file-md5-wasm/js/calc_file_hash.js')}"></script>
  
      <define name="pictures_os_intercept" value="1" />
  </notdefined>
  
  <script>
      $(function () {
          $('input[name="test_os"]').osuploader({
              multi_selection:true,
              url:'{$data_url}',
              vendor_type:'{$vendor_type}',
              cacl_file_hash:'{$cacl_file_hash}',
              viewer_js:1,
              crop:{
                  dragMode: 'move',
                  aspectRatio: 120/120,
                  viewMode: 1,
                  ready: function () {
                      croppable = true;
                  }
              }
      });
      });
  </script>
  </body>
  </html>
  ```
  
  
  
  ###### osuploader option 说明
  
  ```javascript
  <script>
      $(selector).osuploader(option); //selector 为隐藏域
  
      option: {
        url:                //string require  上传图片的地址
        vendor_type:        //string require  供应商类型
        cacl_file_hash:        //string optional  查重开关，0 关闭，1 开启，默认为 1
        viewer_js:        //string optional  图片viewer预览开关，0 关闭，1 开启，默认为 0
        sortable:         //boolean optional 是否开启拖动图片排序功能，默认false 关闭
        multi_selection:    //boolean optional 是否多选
        canvasOption:{       //object optional 配置getCroppedCanvas
            //修改裁剪后图片的背景色 为黑色
            fillColor: '#333',
        } 
        //get more information: https://github.com/fengyuanchen/cropperjs
  
        crop:{              //object optional cropper配置,若存在此项，则裁剪图片,更多配置请参考cropper.js官网
            aspectRatio: 120/120,
            viewMode: 1,
            ready: function () { 
                croppable = true;
            }
        },
        //由于plup_upload内置的filter,出错时会触发Error回调
        //导致上一个上传任务的失败,自定义了 check_image,limit_file_size,用于前端验证文件后缀格式与文件大小
        filters: {                // object optional
             check_image:         // Boolean 是否检查图片类型(若为true: 对于裁剪上传,允许无后缀文件;多选上传,不允许无后缀文件)
             limit_file_size:     // Number 限制文件大小，参考格式：5 * 1024 * 1024
             prevent_duplicates:  // Boolean 是否允许选取重复文件，false：是，true 否，默认为false
        },
        show_msg:           //function optional 展示提示消息的函数,默认为window.alert
        limit:              //number   optional 上传图片张数的限制,默认值32
        tpye:               //string   optional 上传类型 file | image 默认值 image
        beforeUpload:       //function optional 回调 参考回调说明
        filePerUploaded:    //function optional 回调 参考回调说明
        uploadCompleted:    //function optional 回调 参考回调说明
        uploadError:        //function optional 回调 参考回调说明
        deleteFile:         //function optional 回调 参考回调说明
      }
  
  </script>
  ```
  
  备注:
1. cropper：
   
   - <a href="https://fengyuanchen.github.io/cropper/">官网demo</a>  
   - <a href="https://github.com/fengyuanchen/cropper/blob/master/README.md">github</a>

2. 回调说明:
   
   - beforeUpload : 当选中文件时的回调。若返回false,则不添加选中的文件
   - filePerUploaded : 每个文件上传完成，都会触发此回调
   - uploadCompleted : 若多选上传选中3个图，则3个图完成上传才触发此回调
   - uploadError : 上传出错
   - deleteFile : 删除图片
   
   
## osHooks
### 介绍
    上传到云服务商js帮助类的钩子
#### 钩子说明
  
+ genOsParam：请求 policyGet 接口并根据不同的供应商组装对应的上传参数

  参数说明

  | 参数         | 是否必选 | 类型     | 说明                   |
  |------------|------|--------|----------------------|
  | vendorType | 是    | string | 供应商名称                |
  | url        | 是    | string | 请求url，一般为获取上传签名的接口地址 |
  | fileName        | 是    | string | 文件标题                 |
  | file        | 是    | File   | 文件blob               |
  | hashId        | 否    | string | 文件MD5信息，用于查重         |
  | params        | 是    | object | 用于接收返回值的对象           |


  将结果注入 params.osParams 的属性，具体属性说明

  | 参数         | 是否必选 | 类型     | 说明                    |
  |------------|------|--------|-----------------------|
  | url | 是    | string | 上传请求的地址               |
  | multipart_params        | 是    | object | 请求的参数，一般放在 FormData 里 |

  ```javascript
    let params = {};
    osHooks.trigger('genOsParam', vendorType, policyGetUrl, fileName, file, hashId, params)

    return {
        url:params.osParams.url,
        multipart_params:params.osParams.multipart_params
    }
  ```

## Antd-admin富文本使用

```php
$container->ueditor('ueditor', '富文本')
    ->setConfig([
        'serverUrl'=> U('/extends/ueditor/index').'?os=1&type=editor&vendor_type=tengxun_cos',
    ])
    ->setExtraScripts([
        asset('/object-storage/os_upload.js'),
    ])
```

## 自定义回调地址

`.env` 文件中加入配置项 `OBJECT_STORAGE_CALLBACK_URL`，如：`OBJECT_STORAGE_CALLBACK_URL=http://your_domain.com/extends/ObjectStorage/callBack`