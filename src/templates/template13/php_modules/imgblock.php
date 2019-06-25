<?php
/*Блок картинок*/
if ($pageSpeed){ $content = preg_replace('/<a class="imgblock__item(.|\n)*?<\/a>/', '', $content); }

preg_match_all('/(<a[^>]*)data-img="([0-9]*)x([0-9]*)x([0-9]*)"/', $content, $imgblock);

if (count($imgblock[0])>0)
{
	$idblock = $imgblock[4];		//id контейнера

	$j = 0;
	$imgs_mas = array();
	$imgs_mas[$j] = array();
	$imgs_mas[$j][] = array(
		'links' => 		 $imgblock[0][0], 	//Блок
		'links_clear' => $imgblock[1][0], 	//Блок без 'data-img=""'
		'widths' => 	 $imgblock[2][0], 	//Ширина блока
		'heights' => 	 $imgblock[3][0],	//Высота блока
		'irel' =>		 $imgblock[2][0] / $imgblock[3][0]	//Ширина/Высота
	);			

	for ($i = 1; $i < count($idblock); $i++)
	{
		if ($idblock[$i] != $idblock[$i-1]) {$j++; $imgs_mas[$j] = array();}

		$imgs_mas[$j][] = array(
			'links' => 		 $imgblock[0][$i], 	//Блок
			'links_clear' => $imgblock[1][$i], 	//Блок без 'data-img=""'
			'widths' => 	 $imgblock[2][$i], 	//Ширина блока
			'heights' => 	 $imgblock[3][$i],	//Высота блока
			'irel' =>		 $imgblock[2][$i] / $imgblock[3][$i]	//Ширина/Высота
		);
	}

	function calc_width_imgblock($imgs)	//массив
	{
		$i = 0;					//Текущий id
		$end = count($imgs)-1;	//Конец массива
		$icof = 1;				//Коэфициент (если ширина одного блока < 2 - то он удвоится)
		$isumrel = 0;			//Сумма ширины блоков
		$iwidth = array();		//Массив ширины в %
		$irows = array();		//Массив рядов (хранит id картинок в каждом ряде)
		$r = 0;					//Номер ряда

		$width_max = 2;
		if ($end>6) $width_max = 3;
		
		do
		{
			$sumrel = 0; 		  //Сумма соотношений
			$masi = array();	  //Массив id блоков

			//Формируем группы (если 2х мало - добавляем еще)
				do
				{
					$sumrel += $imgs[$i]['irel'];
					$masi[] = $i;
					$i = $i+1;
				}
				while (
					( $sumrel < $width_max && $i < count($imgs) ) 		//Если общая ширина меньше 2
				 || ( $i == $end && $imgs[$i]['irel'] < $width_max+$width_max/10 )	//Если след эл-нт последний и шириной меньше 2.2
				);

			$isumrel += $sumrel;
			if ($sumrel<$width_max) $icof = $width_max;

			//Вычисляем ширину каждого блока в %					
				$all_width = 100;
				for ($j = 0; $j < count($masi)-1; $j++)
				{
					$k = $masi[$j];
					$iwidth[$k] = round($imgs[$k]['irel'] * 100 / $sumrel);
					$all_width -= $iwidth[$k];
				}
				$iwidth[$masi[count($masi)-1]] = $all_width;  //Ширина 1го блока = 100% - ширины всех остальных блоков	

			//Сохраняем id в ряду
				$irows[$r] = $masi;
				$r++;
		}
		while ($i <= $end);

		return array($iwidth, $isumrel*$icof, $irows);
	}

	function width_correct_imgblock($iwidth, $irows)	//Коррекция ширины в ряду
	{
		for ($i = 1; $i<count($irows); $i++)
		{
			$rows1 = $irows[$i-1];
			$rows2 = $irows[$i];
			if (count($rows1)>1 && count($rows2)>1)
			{
				$j2=2;
				$rows1_now = $rows1[0];
				$rows2_now = $rows2[0];
				for ($f=0; $f < 2; $f++)
				{
					$correct = $iwidth[$rows1_now] - $iwidth[$rows2_now];
					if (abs($correct)>0 && abs($correct)<3)
					{
						$iwidth[$rows2_now] = $iwidth[$rows2_now] + $correct;
						$iwidth[$rows2[$j2-1]] = $iwidth[$rows2[$j2-1]] - $correct;							
					}

					$j2=count($rows2)-1;
					$rows1_now = $rows1[count($rows1)-1];
					$rows2_now = $rows2[$j2];
				}
			}
		}
		return $iwidth;
	}

	foreach ($imgs_mas as $imgs)
	{			
		//Проходимся по всем блокам
			list($iwidth, $isumrel, $irows) = calc_width_imgblock($imgs);
			if (count($irows)>1)
			{
				list($iwidth2, $isumrel2, $irows2) = calc_width_imgblock(array_reverse($imgs));

				//Корректируем ширину в рядах
					if ($isumrel > $isumrel2)
					{
						$iwidth = width_correct_imgblock($iwidth2, $irows2);
						$iwidth = array_reverse($iwidth);
					}
					else
					{
						$iwidth = width_correct_imgblock($iwidth, $irows);
					}
			}

		for ($i = 0; $i < count($imgs); $i++)
		{
			$content = str_replace_once($imgs[$i]['links'], $imgs[$i]['links_clear'].' style="width:'.$iwidth[$i].'%"', $content); //Поиск и замена в html
		}
	}
}
?>