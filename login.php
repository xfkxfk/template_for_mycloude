<?php
  session_start ();

  $credfile = dirname (__FILE__) . '/data/cred.php';
  if (isset ($_POST['user']) && isset ($_POST['pass']))
  {
    // hardcoded string compare
    foreach (explode ("\n", file_get_contents ($credfile)) as $line)
    {
      if (strpos ($line, 'user=') === 0)
      {
        $user = trim (substr ($line, 5));
      }
      if (strpos ($line, 'pass=') === 0)
      {
        $pass = trim (substr ($line, 5));
      }
    }

    if (! isset ($user) || ! isset ($pass))
    {
      $msg = '您尚未设置用户名和密码，请点击这里查看帮助';
    }
    else if (strcmp ($_POST['user'], $user) === 0 && strcmp ($_POST['pass'], $pass) === 0)
    {
      $msg = '成功';
      $_SESSION['admin'] = true;
      die(header ('Location: index.html'));
    }
    else
    {
      $msg = '用户名或密码错误';
    }

  }
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>登陆</title>
  <link href="bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="bower_components/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
  <link href="bower_components/metisMenu/dist/metisMenu.min.css" rel="stylesheet">
</head>

<body>

  <div class="container" style="margin-top: 10%;">

    <?php if (isset ($msg)) { ?>
    <div class="row">
      <div class="col-md-4 col-md-offset-4 col-xs-12">
        <div class="alert alert-danger" role="alert">
          <a href="#" class="alert-link"><?= $msg ?></a>
        </div>
      </div>
    </div>
    <?php } ?>

    <div class="row">
      <div class="col-md-4 col-md-offset-4 col-xs-12">
        <div class="panel panel-default">
          <div class="panel-heading">
            <strong class="">管理员登陆</strong>
          </div>
          <div class="panel-body">
            <form class="form-horizontal" role="form" method="POST">
              <div class="form-group">
                <label class="col-sm-3 control-label col-xs-12">用户名</label>
                <div class="col-sm-9 col-xs-12">
                  <input type="text" class="form-control" name="user" placeholder="请输入用户名" required value="admin">
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-3 control-label col-xs-12">密码</label>
                <div class="col-sm-9 col-xs-12">
                  <input type="password" class="form-control" name="pass" placeholder="请输入密码" required value="admin">
                </div>
              </div>
              <div class="form-group last">
                <div class="col-sm-offset-3 col-sm-9 col-xs-12">
                  <button type="submit" class="btn btn-success btn-sm">登陆</button>

                </div>
              </div>
            </form>
          </div>
          <div class="panel-footer">
            忘记密码了？<a href="#" class="">点击这里查看方法</a>
          </div>
        </div>
      </div>
    </div>
  </div>

</body>

</html>
