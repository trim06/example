<?php
/**
 * @var $fileId int ID только что созданного файла
 * @var $contractId ID договора, по которому создается документ
 */
?>

<div>
	<iframe class="google-docs" src="https://docs.google.com/document/d/<?=$fileId;?>/edit" frameborder="0" style="height: 800px; width: 100%; box-shadow: 0 0 10px grey;"></iframe>
</div>
<div class="row" style="margin: 0;">
	<div class="col-sm-8 col-sm-push-4 document-actions" data-contract_id="<?=$contractId;?>" data-file_id="<?=$fileId;?>" data-document_id="<?=$documentId;?>">
		<div class="col-sm-6">
			<button class="btn btn-block btn-sm btn-primary" onclick="main.documents.cancel(this);">Отмена</button>
		</div>
		<div class="col-sm-6">
			<button class="btn btn-block btn-sm btn-success" onclick="main.documents.updateDocument(this);">Сохранить документ</button>
		</div>	
	</div>
</div>


<script type="text/javascript">
	$(document).ready(function() {
		main.documents.resizeDocumentFrame();
	});
</script>
