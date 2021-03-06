<?=load_inline_js('inc/js-meio-mask')?>
<?=load_inline_js('inc/js-init-mask')?>

<?=form_open($BC->_getBaseURI()."/search")?>

<table>
<tr>
	<td><?=language('search')?>:</td>
	<td><?=form_input("keywords",trim(urldecode($keywords)))?></td>
</tr>

<?load_theme_view('inc/tpl-categories-search-select',array('categories_model'=>'products_categories'))?>

<tr>
	<td><?=language('sort_by')?>:</td>
	<td><?=form_dropdown("sort_by",array('pub_date'=>language('publish_date'),'name'=>language('thing_name'),'price'=>language('price'),'views'=>language('popularity')),$sort_by)?></td>
</tr>
<tr>
	<td><?=language('sort_direction')?>:</td>
	<td><?=form_dropdown("sort_order",array('asc'=>language('ascending'),'desc'=>language('descending')),$sort_order)?></td>
</tr>
</table>

<p><?=form_submit("submit",language('search'));?></p>

</form>

<?=load_inline_js('inc/js-selectboxes')?>

<?=load_inline_js('inc/js-load-category',array('parent_category'=>0))?>

<script>
$j(document).ready(function(){
	<?if(!isset($search_category_id)):?>
	load_category(0,false,"products_categories");
	<?endif?>
});
</script>