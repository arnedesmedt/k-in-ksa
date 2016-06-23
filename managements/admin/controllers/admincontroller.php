<?php

namespace Managements\Admin;

use Application\Controller as Application_Controller;

/**
* AdminController
*/
class AdminController extends Application_Controller
{
    public function login()
    {
        if (isset($_POST["password"]) && $_POST["password"] == "informatik") {
            $_SESSION['logged_in'] = true;
            header("Location: /admin/index");
        } else if($_SESSION['logged_in']) {
            $this->template = "index.html";
        } else {
            $this->template = "login.html";
        }
    }

    public function index()
    {
        $this->login();

        if (!empty($this->parameters)) {

            $this->template = "list.html";
            $this->context['verdiep'] = $this->parameters[0];
            $this->context['ban'] = $this->parameters[1]; 

            $query = "SELECT * FROM method WHERE verdiep = '{$this->parameters[0]}' AND ban = '{$this->parameters[1]}' ORDER BY volgorde, id ";
            $result = $this->database->query($query);

            $this->context["methods"] = $this->database->result_to_array($result, "id");
        }
    }

    public function edit()
    {
        $this->login();

        if (!empty($this->parameters)) {
            $this->template = "edit.html";

            if (array_key_exists(2, $this->parameters)) {
                $query = "SELECT * FROM method WHERE id = {$this->parameters[2]}";
                $result = $this->database->query($query);
                $data = $this->database->result_to_array($result, "id");
                $data = $data[$this->parameters[2]];

            } else {
                $data["volgorde"] = 0;
                $data['verdiep'] = $this->parameters[0];
                $data['ban'] = $this->parameters[1];
                // $data['id'] = $this->parameters[2];
            }

            $this->context["method"] = $data;
        }
    }

    public function save()
    {
        $this->login();

        unset($_POST["submit"]);

        if (empty($_POST["volgorde"])) {
            $_POST["volgorde"] = 0;
        }

        if (!empty($_FILES['file']['name'])) {
            $file = '/uploads/'.date('Y-m-d_H:i:s').'_'.$_FILES['file']['name'];
            move_uploaded_file($_FILES['file']['tmp_name'], $_SERVER['DOCUMENT_ROOT'].$file);
            $_POST['file'] = $file;
        }

        if (empty($_POST["id"])) {
            $query = "INSERT INTO method (verdiep,ban,name,volgorde,file) VALUES (
                '{$_POST['verdiep']}',
                '{$_POST['ban']}',
                '{$_POST['name']}',
                {$_POST['volgorde']},
                '{$_POST['file']}'
            )";
        } else {
            $file_update = empty($_FILES) ? "" : ",file = '{$_POST['file']}'";
            $query = "UPDATE method SET
                verdiep = '{$_POST['verdiep']}',
                ban = '{$_POST['ban']}',
                name = '{$_POST['name']}',
                volgorde = {$_POST['volgorde']}
                {$file_update}
                WHERE id = {$_POST["id"]}";
        }

        $this->database->query($query);
        header("Location: /admin/index/{$_POST['verdiep']}/{$_POST['ban']}");
    }

    public function delete()
    {
        $this->login();

        $id = array_pop($this->parameters);
        $query = "DELETE FROM method WHERE id = {$id}";
        $this->database->query($query);
        header("Location: /admin/index/".implode("/", $this->parameters));
    }

}
