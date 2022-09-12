<?php 
	http_response_code(404);
	$showCustom = false;
    var_dump($e);
	if (strpos($e->getMessage(), 'Please logout') !== false || Framework\Registry::getSession()->get('admin_user_id')) {
	    $showCustom = true;
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Oops, 404</title>

		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
		<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
		
		<link href="/assets/css/lib/app.css" rel="stylesheet" type="text/css">

		<script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js"></script>
		<script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
		<link href="https://fonts.googleapis.com/css?family=Fira+Sans" rel="stylesheet">
		<style>
		*{
			font-family: 'Fira Sans'
		}
		</style>
	</head>

	<body>

		<div class="ex-page-content">
		    <div class="container">
		        <div class="row" style='margin-top: 100px;'>
		            <div class="col-lg-6">
						<img src='https://gw.alipayobjects.com/zos/rmsportal/KpnpchXsobRgLElEozzI.svg' width='100%'/>
		            </div>

		            <div class="col-lg-6">
						<div class="antd-pro-components-exception-index-content">
							<h1 style='margin-bottom: 24px;color: #434e59;font-weight: 600;font-size: 72px;line-height: 72px;'>404</h1>
							<div style='margin-bottom: 16px;color: rgba(0, 0, 0, 0.45);font-size: 20px;line-height: 28px'><?php if ($showCustom) { echo $e->getMessage(); } else { echo 'Sorry, the page you visited does not exist'; } ?></div>
							<div class="antd-pro-components-exception-index-actions">
								<a href="<?php if ($showCustom) { echo "/"; } else { echo 'https://trackier.com'; } ?>" class="btn btn-primary">
									<span>Back to home</span>
								</a>
							</div>
						</div>
		            </div>
		        </div>
		    </div>
		</div>

		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
	</body>
</html>
