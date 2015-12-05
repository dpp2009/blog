<?php 


$mysqli=new mysqli('127.0.0.1','root','','blog');
$mysqli->query('set names utf8;');
$query="select * from emlog_blog order by gid DESC ";
$result=$mysqli->query($query);

$list = array();


if ($result) {
	if($result->num_rows>0){   
		$k=1;                                     
		while($r =$result->fetch_array(MYSQL_ASSOC) ){  
			if(is_null($r)) break;
			$myfile = fopen("./json/content/content_{$r['gid']}.json", "w") or die("Unable to open file!");
			$json = json_encode($r);
			fwrite($myfile, $json);
			fclose($myfile);

			$list[$k]['id'] = $r['gid'];
			$list[$k]['title'] = $r['title'];
			$list[$k]['date'] = $r['date'];
			$list[$k]['excerpt'] = $r['excerpt'];
			$k++;
		}
	}
}else {
	echo "查询失败";
}

$myfile = fopen("./json/all_list.json", "w") or die("Unable to open file!");
$json = json_encode($list);
fwrite($myfile, $json);
fclose($myfile);

$myfile = fopen("./json/index_list.json", "w") or die("Unable to open file!");
$json = json_encode(array_slice($list,0,10));
fwrite($myfile, $json);
fclose($myfile);

var_dump(count($list));
die;
die;

$result->free();
$mysqli->close();






