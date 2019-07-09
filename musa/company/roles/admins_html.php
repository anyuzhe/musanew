<div role="main"><span id="maincontent"></span>
<div id="addadmisform">
    <h3 class="main"> <?php echo $company->company_name?> 企业管理员</h3>

    <form id="assignform" method="post" action="/company/roles/admins.php?id=<?php echo $companyid?>">
    <div>
    <input type="hidden" name="sesskey" value="O1cSFNM7io">

    <table class="generaltable generalbox groupmanagementtable boxaligncenter" summary="">
    <tbody><tr>
      <td id="existingcell">
          <p>
            <label for="removeselect">当前企业管理员</label>
          </p>
          <div class="userselector" id="removeselect_wrapper">
<select name="removeselect" id="removeselect" size="20" class="form-control no-overflow">
  <optgroup label="管理员 (<?php echo count($companyadmins)?>)">
    <?php foreach($companyadmins as $admin): ?>
      <option value="<?php echo $admin->id?>">
        <?php echo $admin->username?> (<?php echo $admin->email?>)
      </option>
    <?php endforeach;?>
  </optgroup>
</select>
</div>
          </td>
      <td id="buttonscell">
        <p class="arrow_button">
            <input name="add" id="add" type="submit" value="◄&nbsp;添加" title="添加" class="btn btn-secondary"><br>
            <input name="remove" id="remove" type="submit" value="免除&nbsp;►" title="免除" class="btn btn-secondary"><br>
        </p>
      </td>
      <td id="potentialcell">
          <p>
            <label for="addselect">用户</label>
          </p>
          <div class="userselector" id="addselect_wrapper">
<select name="addselect" id="addselect" size="20" class="form-control no-overflow">
  <optgroup label="可选用户 (<?php echo count($companyusers)?>)">
    <?php foreach($companyusers as $u): ?>
      <option value="<?php echo $u->id?>">
        <?php echo $u->username?> (<?php echo $u->email?>)
      </option>
    <?php endforeach;?>
  </optgroup>
  </optgroup>
</select>
</div>

      </td>
    </tr>
    </tbody></table>
    </div>
    </form>
</div>

</div>