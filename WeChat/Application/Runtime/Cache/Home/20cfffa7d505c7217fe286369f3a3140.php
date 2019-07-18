<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
        <div><img src="<?php echo ($headimgurl); ?>"></div>
        <p>昵称:<input readonly value="<?php echo ($nickname); ?>"></p> 
       <p>国家:<input readonly value="<?php echo ($country); ?>"></p> 
        <p>所在城市:<input readonly value="<?php echo ($province); ?> <?php echo ($city); ?>"></p>
         <p>姓别: <input id="sex" readonly value="<?php echo ($sex); ?>"> </p>
</body>
<script>
    if(document.getElementById('sex').value==1){
        document.getElementById('sex').value='男';
        }else if(document.getElementById('sex').value==2){
            document.getElementById('sex').value='女';
        }else{
            document.getElementById('sex').value=='保密';
        }
    
</script>
</html>