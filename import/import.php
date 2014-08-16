<?php
function	parse_html($str) {
	$result = null;
	for ($i = 0; isset($str[$i]); $i++) {
		if ($str[$i] == '<') {
			while ($str[$i] != '>')
				$i++;
		} else {
			$result .= $str[$i];
		}
	}
	return ($result);
}

function grab_image($url,$saveto){
	$ch = curl_init ($url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
	$raw=curl_exec($ch);
	curl_close ($ch);
	if(file_exists($saveto)){
		unlink($saveto);
	}
	$fp = fopen($saveto,'x');
	fwrite($fp, $raw);
	fclose($fp);
}

$fd = fopen("./product_2014-08-16_014441.csv", "r");

for ($i = 0; $data = fgetcsv($fd, 1000, ";"); $i++) {
	$result[$data[0]]["name"] = $data[2];
	$result[$data[0]]["img"] = $data[1];
	$result[$data[0]]["price"] = $data[6];
	$result[$data[0]]["sku"] = $data[3];
	grab_image($data[1], "../media/import/".explode(' ', $data[2])[0].".jpg");
	echo "Image $i on 114\n";
}

$fd = fopen("./ps_product_lang.csv", "r");

for ($i = 0; $data = fgetcsv($fd, 1000, ","); $i++) {
	$result[$data[0]]["description"] = parse_html($data[4]);
}

$magmi = array();

$magmi[0] = array('sku', 'attribute_set', 'type', 'price', 'name', 'description', 'image', 'small_image', 'short_description', 'weight', 'tax_class_id', 'Visibility');

$v = 1;
foreach ($result as $result) {
	$magmi[$v] = array(
			$result["sku"],
			"Default",
			"simple",
			$result["price"],
			$result["name"],
			$result["description"],
			explode(' ', $result["name"])[0].".jpg",
			explode(' ', $result["name"])[0].".jpg",
			$result["description"],
			"0",
			"None",
			"\"Catalog,Search\""
			);
	$v++;
}

$out = fopen("../var/import/magmi.csv", "a+");

for ($v = 0; isset($magmi[$v]); fputcsv($out, $magmi[$v]), $v++);

?>
