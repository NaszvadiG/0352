<div id="quiz-timer"></div>

<h1><?=$BC->_getPageTitle()?></h1>

<h3><?=language('progress')?>: <?=$amount_answered_questions+1?>/<?=$total_questions?></h3>

<div><?=htmlspecialchars($quiz['question']['question'])?></div>

<?if($quiz['question']['code']):?>
	<div class="code">
		<?=highlight_code($quiz['question']['code'])?>
		<div class="plast"></div>
	</div>
<?endif?>

<br />

<form id="quiz-form" class="form-inline form-horizontal" action="<?=site_url($BC->_getBaseURL().'quiz/submit/'.$quiz['quiz']['id'].'/'.$quiz['question']['id']);?>" method="post">
		
<?foreach ($quiz['answers'] as $answer):?>
	<div>
		<?if($quiz['type']=='input'):?>
			<?=form_input('custom_answer','')?>
		<?elseif($quiz['type']=='checkbox'):?>
			<?=form_checkbox('answers['.$answer['id'].']',1,FALSE,"id='answer_{$answer['id']}'")?> <label for="answer_<?=$answer['id']?>"><?=htmlspecialchars($answer['answer'])?></label>
		<?else:?>
			<?=form_radio('answer',$answer['id'],FALSE,"id='answer_{$answer['id']}'")?> <label for="answer_<?=$answer['id']?>"><?=htmlspecialchars($answer['answer'])?></label>
		<?endif?>
	</div>
<?endforeach;?>

<p><?=form_submit("submit",language('next'));?></p>

</form>

<script>
var time_left = <?=$quiz['question']['time']?>;
var are_you_sure = "<?=language('are_you_sure')?>";
</script>