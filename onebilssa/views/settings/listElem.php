<div class="ltCell cell_page-content sizeParent">
	<div class="ltContainer ltFullWidth sizeChild">
		<div class="ltRow">
			<div class="ltCell cell_page-content_header">
				<h1 class="cell_page-content_header_text">
					<?=$parent['name']?>
				</h1>
			</div>
		</div>
		<div class="ltRow">
			<div class="ltCell cell_page-content_control-panel">
				<a href="/<?=Funcs::$cdir?>/<?=Funcs::$uri[1]?>/showadd/?parent=<?=Funcs::$uri[2]?>" class="button button_add-section">Добавить элемент</a>
			</div>
		</div>
		<div class="ltRow jsScrollContainer">
			<div class="jsScrollWrapper">
				<div class="jsScroll whiteOverflow sections_container">
					<table class="sections_table jsMoveContainerTR" tab="settings" page="<?=$_GET['p']?>">
						<tr class="sections_table_caption_row jsMoveFixed">
							<th class="sections_table_cell">
								<div class="sections_table_caption-holder jsFixedY">
									<span class="sections_table_caption">Название</span>
								</div>
							</th>
							<th class="sections_table_cell">
								<div class="sections_table_caption-holder jsFixedY">
									<span class="sections_table_caption">Алиас</span>
								</div>
							</th>
							<th class="sections_table_cell">
								<div class="sections_table_caption-holder jsFixedY">
									<span class="sections_table_caption">Значение</span>
								</div>
							</th>
							<th class="sections_table_cell">
								<div class="sections_table_caption-holder jsFixedY"></div>
							</th>
							<th class="sections_table_cell">
								<div class="sections_table_caption-holder jsFixedY"></div>
							</th>
						</tr>
						<?foreach($list as $item):?>
							<tr class="sections_table_row jsMoveElement" id="row<?=$item['id']?>">
								<td class="sections_table_cell">
									<div class="sections_table_row-header sections_table_row-header">
										<a class="sections_table_link" href="/<?=Funcs::$cdir?>/<?=Funcs::$uri[1]?>/showedit/?parent=<?=$item['parent']?>&id=<?=$item['id']?>"><?=$item['name']?></a>
									</div>
								</td>
								<td class="sections_table_cell">
									<?=$item['path']?>
								</td>
								<td class="sections_table_cell">
									<?=$item['value']?>
								</td>
								<td class="sections_table_cell">
									<div class="sections_table_control-panel">
										<a href="/<?=Funcs::$cdir?>/<?=Funcs::$uri[1]?>/showedit/?parent=<?=$item['parent']?>&id=<?=$item['id']?>" class="icon icon_edit"></a>
										<span class="icon icon_publish visible <?if($item['visible']):?>active<?endif?>" tab="settings" ids="<?=$item['id']?>"></span>
										<a href="/<?=Funcs::$cdir?>/<?=Funcs::$uri[1]?>/del/?parent=<?=$item['parent']?>&id=<?=$item['id']?>" onclick="return confirm('Удалить элемент?')" class="icon icon_remove"></a>
									</div>
								</td>
								<td class="sections_table_cell">
									<div class="sections_table_move-panel">
										<input class="sections_input_order input_text" value="<?=$item['num']?>" onblur="if($(this).val()!=<?=$item['num']?>){defineSortNum(<?=$item['id']?>,'settings',$(this).val());}">
										<span class="icon_move jsMoveDrag"></span>
									</div>
								</td>
							</tr>
						<?endforeach?>
					</table>
				</div>
			</div>
		</div>
		<div class="ltRow">
			<div class="sections_footer clearfix">
				<?=PaginationWidget::run($pagination)?>
			</div>
		</div>
	</div>
</div>