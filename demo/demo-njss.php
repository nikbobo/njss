<?php
/*
 * PHP SDK Demo
 * Created on 2013-6-18
 *
 */
require_once dirname(__FILE__).'/../sdk/JingdongStorageService.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Asia/Shanghai');

define('ACCESS_KEY', '1bcdaf75cd78411da44aecb532f295cc');
define('ACCESS_SECRET', 'd2db473582f349d894bbd47cda0d6799Bjl5edl7');
					    
function info($title) {
    echo "========= {$title} =========\n";
}

function success($message, $data=null) {
    $dt = date('c');
    if ($data === null) {
        echo "[{$dt}] - {$message}\n\n";
    } else {
        echo "[{$dt}] - {$message} => ";
        print_r($data);
        echo "\n";
    }
}

function exception($message, $e) {
    $dt = date('c');
    $space = str_pad('', (strlen("[{$dt}] - ") - strlen("[Errno] - ")));

    echo "[{$dt}] - {$message}\n";
    echo "{$space}[Errno] - " . $e->getCode() . "\n";
    echo "{$space}[Error] - " . $e->getMessage() . "\n\n";
    print_r($e->to_array());
}


/*
 * 新建bucket
 *
 * 如果创建成功则返回true，否则抛出异常
 * 可以通过异常对象的getCode()方法和getMessage()方法获取对应的错误码和错误信息
 *
 * 注意：bucket名称全局唯一，当名称已存在时则抛出异常
 */
 
// 实例化JingdongStorageService对象
$storage = new JingdongStorageService(ACCESS_KEY,ACCESS_SECRET);

/*
 * 获取所有bucket列表
 ×
 * 如果成功则返回JSBucket列表，否则抛出异常
 × 可以通过异常对象的getCode()方法和getMessage()方法获取对应的错误码和错误信息
 */
function list_buckets_test(){
	global $storage;
	try {
	   $bucketslist = $storage->list_buckets();
	   foreach($bucketslist as $jss_bucket) {
	   	  print_r("Bucket:" . $jss_bucket->get_name() . "\n");
	   	  print_r("CTime: " . $jss_bucket->get_ctime()  . "\n\n");  
	   }
	  // success('Your buckets', $bucketslist);
	
	} catch (Exception $e) {
	    exception('Get buckets failed!', $e);
	}
}


/*
 * 新建bucket
 *
 * 如果创建成功则返回JSSResponse，否则抛出异常
 * 可以通过异常对象的getCode()方法和getMessage()方法获取对应的错误码和错误信息
 *
 * 注意：bucket名称全局唯一，当名称已存在时则抛出异常
 */
function put_bucket_test($bucket_name) {
    global $storage;
	try {
	   // $storage->put_bucket($bucket_name);
	    $storage->put_bucket($bucket_name);
	    success("Put bucket({$bucket_name}) success!");
	} catch (JSSError $e) {
	   echo "HTTP Code : ".$e->getCode()."\n"; //获取http返回错误码
	   echo "Error Code : ".$e->getErrorCode()."\n";  //获取错误信息码
	   echo "Error Message:".$e->getErrorMessage()."\n";//获取错误信息
	   echo "RequestId : ".$e->getRequestId()."\n"; //获取请求ID
	   echo "requestResource : ".$e->getResource()."\n";//获取请求资源
	} catch (Exception $e) {
		echo "Error Code : ".$e->getCode()."\n";
		echo "Error Message : ".$e->getMessage()."\n";
	}
}

/*
 * 删除bucket
 ×
 * 如果删除成功则返回true，否则抛出异常
 × 可以通过异常对象的getCode()方法和getMessage()方法获取对应的错误码和错误信息
 *
 * 注意：如果bucket内容非空则无法删除！！！
 */
  
function delete_bucket_test($bucket_name) {
    global $storage;
	try {
	    $jss_response = $storage->delete_bucket($bucket_name);
	
	    success("Delete bucket({$bucket_name}) success!");
	    print_r($jss_response->to_array());
	
	} catch (Exception $e) {
	    exception("Delete bucket({$bucket_name}) failed!", $e);
	}
}

/*
 * 获取bucket访问控制信息
 *
 * 如果成功返回Bucket policy的信息（json），否则抛出异常
 * 可以通过异常对象的getCode()方法和getMessage()方法获取对应的错误码和错误信息
 *
 */
function get_bucket_policy($bucket_name) {
	global $storage;
	try {
	    $jss_response = $storage->get_bucket_policy($bucket_name);
	    $bucket_policy = $jss_response->get_body();
	    success("Your bucket({$bucket_name}) policy", json_decode($bucket_policy, true));
	
	} catch (Exception $e) {
	    exception("Get bucket({$bucket_name}) policy failed!", $e);
	}
}


function put_object_test($bucket_name,$key){

    global $storage;
	try {
	    // put_object()方法可以接收一个文件路径
	    $object_name = 'test.jpg';
	    $local_file = dirname(__FILE__) . '/logo.png';
	    $storage->put_object($bucket_name,$key, $local_file);
	
	    success("Put object success!");
	
	} catch (Exception $e) {
	    exception('Put object failed!', $e);
	}
	
	// 使用stream方式新建object
	// put_object()方法可以接收一个stream对象作为输入
	try {
	    $object_name = 'test2.jpg';
	    $local_stream = fopen(dirname(__FILE__) . '/logo.png', 'rb');
	    $storage->put_object($bucket_name,$object_name, $local_stream);
	
	    success("Put object success!");
	
	} catch (Exception $e) {
	    exception('Put object failed!', $e);
	}
}


/*
 * 获取object
 ×
 * 如果获取成功则返回true，否则抛出异常
 × 可以通过异常对象的getCode()方法和getMessage()方法获取对应的错误码和错误信息
 *
 * 注意：首先，确保脚本对本地文件系统具有可写权限；其次，如果本地已存在同名文件，该操作将会覆盖本地文件内容！！！
 */
function get_object_test($bucket_name,$key) {
	
	global $storage;
	try {
	    // 这里我们将put_object示例中创建的test.jpg对象保存为本地tmp_logo.jpg文件，
	    // 这样就能正确的浏览本地文件了。
	    $local_file = dirname(__FILE__) . '/tmp_logfdo.jpg';
	    $other_headers = array();     //可传入如Range等其他该请求可用的request header
	    $storage->get_object($bucket_name,$key, $local_file,$other_headers);
	
	    success("Get object success!");
	
	} catch (Exception $e) {
	    exception('Get object failed!', $e);
	}
	
	
	// 使用stream方式新建object
	// get_object()方法可以接收一个stream对象作为输出
	try {
	    $local_file = dirname(__FILE__) . '/tmp_logo_stream.jpg';
	
	    $local_fp = fopen($local_file, 'wb');
	    if ($local_fp) {
	        $auto_close_stream = false;
	
	        $storage->get_object($bucket_name,$key,$local_fp, $auto_close_stream);
	
	        // close the stream manual
	        fclose($local_fp);
	
	        success("Get object success!");
	    } else {
	        info("Oops~, cannot open {$local_file}");
	    }
	
	} catch (Exception $e) {
	    exception('Get object failed!', $e);
	}
	
}


/*
 * 获取object meta
 ×
 * 如果object存在则返回JSSResponse，否则抛出异常
 × 可以通过异常对象的getCode()方法和getMessage()方法获取对应的错误码和错误信息
 */

function head_object_test($bucket_name,$key) {
    global $storage;	
	try {
	    $jss_response = $storage->head_object($bucket_name,$key);	
	    success("Meta of {$key} is", $jss_response->get_headers());
	
	} catch (Exception $e) {
	    exception('Head object failed!', $e);
	}
}

function list_objects_test($bucket) {
	global $storage;	
	try {
		$options = array(
	      "marker" => '',
	      "maxKeys" => 100,
	      "prefix" => 'a',
	      "delimiter" => '', 
	      );
	      $jssentity = $storage->list_objects($bucket,$options);
	      $objects = $jssentity->get_object(); 
	      foreach($objects as $object) {
	      	print_r($object->get_key()."\n");
	      	//print_r($object->get_size()."\n");   
	      	//print_r($object->get_etag()."\n");
	      	//print_r($object->get_last_modified()."\n");
	      }
	      success("Your objects of {$bucket}", $jssentity);
	} catch (Exception $e) {
		  exception("Get objects of {$bucket} failed!", $e);
	}

}

/*
 * 删除object
 ×
 * 如果删除成功则返回true，否则抛出异常
 × 可以通过异常对象的getCode()方法和getMessage()方法获取对应的错误码和错误信息
 */

function delete_object_test($bucket,$key) {
	global $storage;
	try {
	    $storage->delete_object($bucket,$key);
	    success("Delete object success!");
	
	} catch (Exception $e) {
	    exception('Delete object failed!', $e);
	}
}


function delete_bucket_force($bucket) {
	global $storage;
	try {
	   while(true) {
	    	$jss_entity = $storage->list_objects($bucket);
	    	$object_list = $jss_entity->get_object();
	    	if(empty($object_list)) {
	    		echo "{$bucket} is empty now\n";
	    		break;
	    	}
	    	
	    	foreach($object_list as $object){
	    		$storage->delete_object($bucket,$object->get_key());
	    		echo "Delete object {$object->get_key()} success!\n";
	    	}
	    }
	
	} catch (Exception $e) {
	    exception('Delete bucket failed!', $e);
	}
}

function get_object_resource($bucket,$key){
	global $storage;
	try {
		$expire = 10*60; //十分钟后失效
		$url = $storage->get_object_resource($bucket,$key,$expire);
		success("the url is:".$url."\n");
		
	} catch (Exception $e) {
		exception('Get object resource failed!', $e);
	}
}


function demo() {
	$service = new JingdongStorageService(ACCESS_KEY,ACCESS_SECRET);
	$bucket = 'test'.substr(md5(uniqid(mt_rand(), true)),0,5);
	$local_file = dirname(__FILE__) . '/logo.png';
	$key = "logo.png";
	try{
		// list bucket
		$service->list_buckets();  
		//create new  bucket
		$service->put_bucket($bucket);
		//put object
		$service->put_object($bucket,$key,$local_file);
	    //head object
	    $service->head_object($bucket,$key);
	    //get object
	    $service->get_object($bucket,$key,dirname(__FILE__) . "/".$key);
	    //get pre-sign url
	    $url = $service->get_object_resource($bucket,$key);
	    echo "url for {$bucket}/{$key} :".$url."\n";
	     
	    // list objects
	    $jss_entity = $service->list_objects($bucket); 
		$object_list = $jss_entity->get_object();
		foreach($object_list as $object){
			// delete object
		    $service->delete_object($bucket,$object->get_key()); 
		    echo "Delete object {$object->get_key()} success!\n";
		}
		// delete bucket
		$service->delete_bucket($bucket);
    } catch (JSSError $e) {
	   echo "HTTP Code : ".$e->getCode()."\n"; //获取http返回错误码
	   echo "Error Code : ".$e->getErrorCode()."\n";  //获取错误信息码
	   echo "Error Message:".$e->getErrorMessage()."\n";//获取错误信息
	   echo "RequestId : ".$e->getRequestId()."\n"; //获取请求ID
	   echo "requestResource : ".$e->getResource()."\n";//获取请求资源
	   delete_bucket_force($bucket);
	} catch (Exception $e) {
		echo "Error Code : ".$e->getCode()."\n";
		echo "Error Message : ".$e->getMessage()."\n";
		delete_bucket_force($bucket);
	}
}

function test_total(){
	$service = new JingdongStorageService(ACCESS_KEY,ACCESS_SECRET);
	$bucket = 'test'.substr(md5(uniqid(mt_rand(), true)),0,5);
	$local_file = dirname(__FILE__) . '/logo.png';
	try{
		$service->list_buckets();  // list bucket
		$service->put_bucket($bucket); //create new  bucket
		foreach(range(0,10) as $i) {     // put object
			$key = "test_".$i.".logo.png";
		    $service->put_object($bucket,$key,$local_file);
		    $service->head_object($bucket,$key);
		    $service->get_object($bucket,$key,dirname(__FILE__) . "/".$key);
		    $url = $service->get_object_resource($bucket,$key);
		    echo "url for {$bucket}/{$key} :".$url."\n";
		}
	      
	    while(true) {
	    	$jss_entity = $service->list_objects($bucket);  // list objects
	    	$object_list = $jss_entity->get_object();
	    	if(empty($object_list)) {
	    		echo "{$bucket} is empty now\n";
	    		break;
	    	}
	    	
	    	foreach($object_list as $object){
	    		$service->delete_object($bucket,$object->get_key()); 
	    		echo "Delete object {$object->get_key()} success!\n";
	    	}
	    }
	    
	    $service->delete_bucket($bucket);
	    
	} catch (JSSError $e) {
	   echo "HTTP Code : ".$e->getCode()."\n"; //获取http返回错误码
	   echo "Error Code : ".$e->getErrorCode()."\n";  //获取错误信息码
	   echo "Error Message:".$e->getErrorMessage()."\n";//获取错误信息
	   echo "RequestId : ".$e->getRequestId()."\n"; //获取请求ID
	   echo "requestResource : ".$e->getResource()."\n";//获取请求资源
	   delete_bucket_force($bucket);
	} catch (Exception $e) {
		echo "Error Code : ".$e->getCode()."\n";
		echo "Error Message : ".$e->getMessage()."\n";
		delete_bucket_force($bucket);
	}
}

//demo();
test_total();
//$bucket = 'test'.substr(md5(uniqid(mt_rand(), true)),0,5);
//list_buckets_test();
//put_bucket_test($bucket);
//delete_bucket_test($bucket);

//put_object_test($bucket,"logo.png");
//get_object_test($bucket,'test.jpg');
//head_object_test($bucket,'test2.jpg');
//list_objects_test($bucket);
//delete_object_test($bucket,'test.jpg');
//get_object_resource($bucket,'test2.jpg');




