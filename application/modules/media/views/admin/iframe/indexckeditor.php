<link href="<?=$this->getBaseUrl('application/modules/media/static/css/media.css'); ?>" rel="stylesheet">
<?php if ($this->get('medias') != '') {?>
<div id="ilchmedia" class="container-fluid">
    <?php if( $this->getRequest()->getParam('type') === 'image' OR $this->getRequest()->getParam('type') === 'single'){ ?>
        <?php foreach ($this->get('medias') as $media) : ?>
            <?php if(in_array($media->getEnding() , explode(' ',$this->get('media_ext_img')))){
                echo '<div  id="'.$media->getId().'" class="col-lg-2 col-sm-3 col-xs-4 media_loader"><img class="image thumbnail img-responsive" data-url="'.$media->getUrl().'" src="'.$this->getBaseUrl($media->getUrlThumb()).'" alt=""></div>';
                }
            ?>
        <?php endforeach; ?>
    <?php }  ?>

    <?php if( $this->getRequest()->getParam('type') === 'media'){ ?>
        <?php foreach ($this->get('medias') as $media) : ?>
            <?php if( in_array($media->getEnding() , explode(' ',$this->get('media_ext_video')))){
                echo '<div class="col-lg-2 col-sm-3 col-xs-4"><img class="image thumbnail img-responsive" data-url="'.$media->getUrl().'" src="'.$this->getBaseUrl('application/modules/media/static/img/nomedia.png').'" alt=""><div class="media-getending">Type: '.$media->getEnding().'</div><div class="media-getname">'.$media->getName().'</div></div>';
                }
            ?>
        <?php endforeach; ?>
    <?php }  ?>

    <?php if( $this->getRequest()->getParam('type') === 'file'){ ?>
        <?php foreach ($this->get('medias') as $media) : ?>
            <?php if( in_array($media->getEnding() , explode(' ',$this->get('media_ext_file')))){
                echo '<div id="'.$media->getId().'" class="col-lg-2 col-sm-3 col-xs-4 media_loader"><img class="image thumbnail img-responsive" data-alt="'.$media->getName().'" data-url="'.$media->getUrl().'" src="'.$this->getBaseUrl('application/modules/media/static/img/nomedia.png').'" alt=""><div class="media-getending">Type: '.$media->getEnding().'</div><div class="media-getname">'.$media->getName().'</div></div>';
                }
            ?>
        <?php endforeach; ?>
    <?php }  ?>
</div>

<?php
} else {
    echo $this->getTrans('noMedias');
}
?>
<?php if( $this->getRequest()->getParam('type') === 'image' or $this->getRequest()->getParam('type') === 'single'){ ?>
<script>
    $(".image").click(function(){
        var dialog = window.top.CKEDITOR.dialog.getCurrent();
        dialog.setValueOf('tab-basic','src', '<?=$this->getBaseUrl()?>'+$(this).data('url'));
        window.top.$('#MediaModal').modal('hide');
    });
</script>
<?php } ?>
<?php if( $this->getRequest()->getParam('type') === 'file'){ ?>
<script>
    $(".image").click(function(){
        var dialog = window.top.CKEDITOR.dialog.getCurrent();
        dialog.setValueOf('tab-adv','file', '<?=$this->getBaseUrl()?>'+$(this).data('url'));
        dialog.setValueOf('tab-adv','alt', $(this).data('alt'));
        window.top.$('#MediaModal').modal('hide');
    });
</script>
<?php } ?>
<?php if( $this->getRequest()->getParam('type') === 'media'){ ?>
<script>
    $(".image").click(function(){
        var dialog = window.top.CKEDITOR.dialog.getCurrent();
        dialog.setValueOf('tab-mov','video', '<?=$this->getBaseUrl()?>'+$(this).data('url'));
        window.top.$('#MediaModal').modal('hide');
    });
</script>
<?php } ?>
<?php if( $this->getRequest()->getParam('type') === 'single'){ ?>
<script>
    $(".image").click(function(){
        window.top.$('#selectedImage').val($(this).data('url'));
        window.top.$('#MediaModal').modal('hide');
    });
</script>
<?php } ?>

<script type="text/javascript">
    $(document).ready(function()
    {
	function media_loader() 
        { 
            var ID=$(".media_loader:last").attr("id");
            $.post("<?=$this->getUrl('admin/media/ajax/index/type/')?><?=$this->getRequest()->getParam('type')?>/pageid/"+ID,
		function(data)
                {
                    if (data !== "")
                    {
                        $(".media_loader:last").after(data);
                    }
                }
            );
        };  

        $(window).scroll(function()
        {
            if ($(window).scrollTop() === $(document).height() - $(window).height())
            {
                media_loader();
            }
        }); 
    });
</script>