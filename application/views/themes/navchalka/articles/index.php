<?
    // table settings
	$BC->load->library('table');
	
    $tmpl = array (
        'table_open'        => '<table id="articles-list" width="100%">',
        'cell_start'        => '<td>',
        'cell_alt_start'    => '<td>',
  	);

	$BC->table->set_template($tmpl); 
	
	// --- BUILD TABLE --- //
	if(!empty($posts_list)) 
	{
		foreach ($posts_list as $key=>$record)
		{
			$date = date('d/m/Y H:i',strtotime($record->pub_date));
		    
		    $posts_list[$key] = "
			<h2>{$record->head}</h2>
			
			<div class='fl'><i>{$date}</i></div>
			<div class='fr'><i>".comments_number('articles',$record->id)."</i></div>
			<div class='clearfix'></div>
			
			<div class='well'>
	
				".word_limiter_more($record->body,80)."
	
				".anchor_base('articles/name/'.$record->slug,language('read_more'))."
	
			</div>
			";
		    
		    //add tags
		    $posts_list[$key] .= load_theme_view('inc/box-post-tags',array('post_id'=>$record->id),TRUE);
		}
	}

	if(isset($posts_list) && !empty($posts_list)) 
	{
		$posts_list = $BC->table->make_columns($posts_list, 1);
		$content_table = $BC->table->generate($posts_list);
	}
	else 
	{
		$content_table = "<h2>".language('search_did_not_give_any_results')."</h2>";
		$paginate = "";
	}
?>

<h1><?=$BC->_getPageTitle()?></h1>

<div>
	<?if(@trim(urldecode($keywords))):?>
    <div class="well search-results-for">
        <h4><?=language('search_results_for')?>: " <i><?=trim(urldecode($keywords))?></i> "</h4>
    </div>
    <?elseif(@trim(urldecode($tag))):?>
    <div class="well search-results-for">
        <h4><?=language('articles_with_tag')?>: " <i><?=trim(urldecode($tag))?></i> "</h4>
    </div>
    <?endif?>

	<?=$content_table?>
	
	<?if($paginate):?>
    <div class="pagination"><?=$paginate?></div>
    <?endif?>
</div>