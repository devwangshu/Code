<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>IIT ISM Login Registration</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" media="screen" title="no title">
  </head>
  <body>

    <div class="container">
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <div class="login-panel panel panel-success">
                <div class="panel-heading">
                    <h3 class="panel-title">Add Employee Here</h3>
                </div>
                

                <div class="panel-body">
                    <form role="form" method="post" action="<?php echo base_url('index.php/MyController/add_employee_db'); ?>">
                        <fieldset>
                            <div class="form-group"  >
                                <input class="form-control" placeholder="Emp ID" name="emp_id" type="text" autofocus>
                            </div>
                            <div class="form-group">
                                <input class="form-control" placeholder="Name" name="emp_name" type="text" value="">
                            </div>


                                <input class="btn btn-lg btn-success btn-block" type="submit" value="save" name="Save" >

                        </fieldset>
                    </form>
                

                </div>
            </div>
        </div>
    </div>
</div>


  </body>
</html>