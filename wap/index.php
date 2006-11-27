<?php

  if(isset($_GET['uid'])) {
    header("Location: http://valar.technikum-wien.at:8080/InfoTerminal/wap?uid=" . $_GET['uid'] );
    header("Content-type: text/vnd.wap.wml");
  } else {
    header("Location: http://valar.technikum-wien.at:8080/InfoTerminal/wap");
    header("Content-type: text/vnd.wap.wml");
  }
?> 