<?php
/*
 对二维数组进行排序
MysqlQsort($array,$fieldname)
MysqlQsortDesc($array,$fieldname)
*
/** 对多维数组的排序(主要用于对Mysql搜索得到的数组)
 */
/**  交换2个数值 swap($v1,$v2)
 * @param  $v1               要交换的第一个值
 * @param  $v2               要跟v1交换的值
 */
function swap(&$v1,&$v2){
	$tmp    = $v1;
	$v1     = $v2;
	$v2     = $tmp;
}//
/**  得快速排序算法的中值 Median3($mysql,$field)
 * @param  array   $mysql       要排序的mysql数组
 * @param  string  $field        要根据field来排序mysql的数组
 * @return mysql的中值
 */
function MysqlMedian3(&$mysql,$field,$left = null, $right = null){
	if(null == $left)
		$left   = 0;
	if(null == $right)
		$right  = count($mysql)-1;

	$center = ceil(($left+$right)/2);

	if($mysql[$left][$field] > $mysql[$center][$field]){//如果左边的值大于中值
		swap(&$mysql[$left],&$mysql[$center]);
	}
	if($mysql[$left][$field] > $mysql[$right][$field])
		swap(&$mysql[$left],&$mysql[$right]);
	if($mysql[$center][$field] > $mysql[$right][$field])
		swap(&$mysql[$center],&$mysql[$right]);

	//现在应该是    $mysql[left] <=  $mysql[center]   <=  $mysql[right]

	swap(&$mysql[$center],&$mysql[$right-1]);
	return $mysql[$right - 1][$field];
}//
/**  对mysql数组根据某个field进行快速排序 MysqlQsort($mysql,$field)
 * @param  array   $mysql  就修改这个值
 * @return null
 */
function MysqlQsort(&$mysql,$field,$left = null,$right = null){
	if(count($mysql) <= 1)
		return true;
	if(null == $left)
		$left   = 0;
	if(null == $right)
		$right  = count($mysql)-1;

	if($left + 15 <= $right){//数组长度小于15采用插 入排序实现
		//1:选择中心点
		$pivot  = MysqlMedian3(&$mysql,$field,$left,$right);
		$i      = $left;
		$j      = $right - 1;//目前中心点的位置
		for( ; ; ){
			//2:从I开始向后搜索，即由前开始向后搜索（I：=I+1），找到第一个大于pivot，两者交换；
			//只要值小于中心点的值就右移，停下时i的位置就是第一个大于pivot的位置
			while($mysql[++$i][$field] < $pivot){}
			//只要值大于中心点的值就左移，停下时j的位置就是第一个小于pivot的位置
			while($mysql[--$j][$field] > $pivot){}
			if($i < $j)
				swap(&$mysql[$i],&$mysql[$j]);
			else
				break;
		}
		swap(&$mysql[$i],&$mysql[$right - 1]); //从新存储中值点
		MysqlQsort(&$mysql, $field,$left, $i-1);
		MysqlQsort(&$mysql, $field,$i+1, $right);
	}
	else
		MysqlInsertionSort(&$mysql,$field,$left,$right);
}
/**  对mysql数组采用插入排序发排序 MysqlInsertionSort($mysql,$field,$left,$reigh)
 */
function MysqlInsertionSort(&$mysql,$field,$left = null,$right=null){
	if(null == $left)
		$left   = 1;
	if(null == $right)
		$right  = count($mysql);
	for($p=$left;$p<=$right;$p++){
		$tmp = $mysql[$p];
		for($j=$p; $j>0 && $mysql[$j-1][$field]>$tmp[$field]; $j--)
			$mysql[$j] = $mysql[$j - 1];
		$mysql[$j] = $tmp;
	}
}
/**  (desc)得快速排序算法的中值 Median3Desc($mysql,$field)
 * @param  array   $mysql       要排序的mysql数组
 * @param  string  $field        要根据field来排序mysql的数组
 * @return mysql的中值
 */
function MysqlMedian3Desc(&$mysql,$field,$left = null, $right = null){
	if(null == $left)
		$left   = 0;
	if(null == $right)
		$right  = count($mysql)-1;

	$center = ceil(($left+$right)/2);

	if($mysql[$left][$field] < $mysql[$center][$field]){//如果左边的值大于中值
		swap(&$mysql[$left],&$mysql[$center]);
	}
	if($mysql[$left][$field] < $mysql[$right][$field])
		swap(&$mysql[$left],&$mysql[$right]);
	if($mysql[$center][$field] > $mysql[$right][$field])
		swap(&$mysql[$center],&$mysql[$right]);

	//现在应该是    $mysql[left] <=  $mysql[center]   <=  $mysql[right]

	swap(&$mysql[$center],&$mysql[$right-1]);
	return $mysql[$right - 1][$field];
}//
/**  (desc)对mysql数组根据某个field进行快速排序 MysqlQsortDesc($mysql,$field)
 * @param  array   $mysql
 * @return array
 */
function MysqlQsortDesc(&$mysql,$field,$left = null,$right = null){
	if(count($mysql) <= 1)
		return true;
	if(null == $left)
		$left   = 0;
	if(null == $right)
		$right  = count($mysql)-1;

	if($left + 10 <= $right){//数组长度小于10采用插 入排序实现
		//1:选择中心点
		$pivot  = MysqlMedian3Desc(&$mysql,$field,$left,$right);
		$i      = $left;
		$j      = $right - 1;//目前中心点的位置
		for( ; ; ){
			//2:从I开始向后搜索，即由前开始向后搜索（I：=I+1），找到第一个大于pivot，两者交换；
			//只要值小于中心点的值就右移，停下时i的位置就是第一个大于pivot的位置
			while($mysql[++$i][$field] > $pivot){}
			//只要值大于中心点的值就左移，停下时j的位置就是第一个小于pivot的位置
			while($mysql[--$j][$field] < $pivot){}
			if($i < $j)
				swap(&$mysql[$i],&$mysql[$j]);
			else
				break;
		}
		swap(&$mysql[$i],&$mysql[$right - 1]); //从新存储中值点
		MysqlQsortDesc(&$mysql, $field,$left, $i-1);
		MysqlQsortDesc(&$mysql, $field,$i+1, $right);
	}
	else
		MysqlInsertionSortDesc(&$mysql,$field,$left,$right);
}//
/**  (desc)对mysql数组采用插入排序发排序 MysqlInsertionSort($mysql,$field,$left,$reigh)
 */
function MysqlInsertionSortDesc(&$mysql,$field,$left = null,$right=null){
	if(null == $left)
		$left   = 1;
	if(null == $right)
		$right  = count($mysql);
	for($p=$left;$p<=$right;$p++){
		$tmp = $mysql[$p];
		for($j=$p; $j>0 && $mysql[$j-1][$field]<$tmp[$field]; $j--)
			$mysql[$j] = $mysql[$j - 1];
		$mysql[$j] = $tmp;
	}
}//
?>
