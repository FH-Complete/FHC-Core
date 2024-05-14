<div id="<?=$id?>" style="<?=$style?>"></div>
<script>
  var container = document.getElementById('<?=$id?>');

  var options = {
    mode: '<?=$mode?>',
    modes: [<?=$modes?>], 
    onError: function (err) {
      alert(err.toString());
    },
    onModeChange: function (newMode, oldMode) {
      console.log('Mode switched from', oldMode, 'to', newMode);
    }
  };

  var json = <?=$json?>;

  var <?=$vareditor?> = new JSONEditor(container, options, json);
</script>


