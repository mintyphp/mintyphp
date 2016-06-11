<?php
namespace MindaPHP;

class Graph
{
	public static function verticalBar($values,$height,$title='',$description='') {
		$real_max = max($values);
		$max = pow(10,ceil(log10($real_max)));
		while ($max/2>$real_max) $max/=2;
		$html = '<div>';
		$html.= '<div style="position: relative; clear: both; text-align: center;">';
		$html.= $title.'</div>';
		for ($i=0;$i<10;$i++) {
			if ($i%2==0) {
				$html.= '<div style="position: relative; top: '.($i/10*$height).'px; width: 100%;">';
				$html.= '<div style="position: absolute; width: 100%; text-align: left; border-top: 1px solid #aaa;">';
				$html.= '&nbsp;'.((1-$i/10)*$max);
				$html.= '</div>';
				$html.= '<div style="position: absolute; width: 100%; text-align: right; border-top: 1px solid #aaa;">';
				$html.= ((1-$i/10)*$max).'&nbsp;';
				$html.= '</div>';
				$html.= '</div>';
			} else {
				$html.= '<div style="position: relative; top: '.($i/10*$height).'px; width: 100%;">';
				$html.= '<div style="position: absolute; width: 100%; text-align: left; border-top: 1px solid #ccc;">';
				$html.= '</div>';
				$html.= '</div>';
			}
		}
		$c = count($values);
		foreach ($values as $key=>$value) {
				$p = round(100*($value/$max));
				$title = is_string($key)?$key.': '.$value:$value;
				$html.= '<div style="float: right; width: '.(100/$c).'%; height: '.$height.'px;">';
				$html.= '<div style="width: 100%; height: 100%; background-color: #eee;">';
				$html.= '<a style="display: block; position: relative; margin: 0 10%; background-color: #aaa; height: '.$p.'%; top: '.(100-$p).'%" title="'.$title.'">';
				$html.= '</a>';
				$html.= '</div>';
				$html.= '</div>';
		}
		$html.= '<div style="position: relative; clear:both; border-top: 1px solid #aaa;">';
		$html.= $description.'</div>';
		$html.= '</div>';
		return $html;
	}
	
	public static function horizontalBar($values,$height,$title='') {
		$real_max = max($values);
		$max = pow(10,ceil(log10($real_max)));
		while ($max/2>$real_max) $max/=2;
		$html = '<div>';
		$html.= '<div style="position: relative; clear: both; text-align: center;">';
		$html.= $title.'</div>';
		$html.= '<div style="position: relative; height: '.$height.'px; background-color: #eee;">';
		for ($i=0;$i<10;$i++) {
			if ($i%2==0) {
				$html.= '<div style="position: absolute; left: '.($i*10).'%; height: 100%; text-align: left; border-left: 1px solid #aaa;">';
				$html.= '</div>';
				$html.= '<div style="position: absolute; left: '.($i*10).'%; top: 100%;">';
				$html.= '&nbsp;'.(($i/10)*$max);
				$html.= '</div>';
			} else {
				$extra = $i==9?' border-right: 1px solid #aaa; width: 10%;':'';
				$html.= '<div style="position: absolute; left: '.($i*10).'%; height: 100%; text-align: left; border-left: 1px solid #ccc;'.$extra.'">';
				$html.= '</div>';
			}
		}
		$html.= '</div>';
		$c = count($values);
		$row = ($height/$c);
		$margin = .1*$row;
		$line = $row-2*$margin;
		$html.= '<div style="position: relative; margin-top: -'.$height.'px; height: '.$height.'px;">';
		foreach ($values as $key=>$value) {
				$p = round(100*($value/$max));
				$title = is_string($key)?$key.': '.$value:$value;
				$html.= '<div style="position: relative; height: '.$row.'px">';
				$html.= '<div style="width: 100%; height: 100%; padding:'.$margin.'px 0">';
				$html.= '<a style="display: block; float: left; position: relative; background-color: #aaa; width: '.$p.'%; height: 100%;" title="'.$title.'">';
				$html.= '</a>';
				$html.= '<div style="float: left; position: relative; width: '.(100-$p).'%; height: 100%; line-height: '.$line.'px; overflow: hidden;">';
				$html.= '&nbsp;'.(is_string($key)?$key:'').'</div>';
				$html.= '</div>';
				$html.= '</div>';
		}
		$html.= '</div>';
		$html.= '<div style="position: relative;">';
		$html.= '&nbsp;</div>';
		$html.= '</div>';
		return $html;
	}

}