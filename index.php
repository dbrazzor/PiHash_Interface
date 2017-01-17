<DOCTYPE html>

    <html>

    <head>

        <title>PiHash - Interface</title>
        <meta charset="UTF-8"/>
        <link rel="stylesheet" href="styles/style.css"/>

    </head>


    <body>

    <div id="index_parent" style="width: 100%">

        <?php

        $servername = "localhost";
        $username = "root";
        $password = "mysqlpassword";
        $dbname = "Hashed_Passwords";

        $key = "";
        $value = "";

        $case_sensitive = false;

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $request = "SELECT * FROM Passwords WHERE ";
        $post_request = "";
        $unknown_entry_var = false;
        $valid = false;
        $fields_names = array();

        $action_value = "";

        if ($_GET) {

            $val = $_GET["q"];

            foreach ($_GET as $k => $v) {

                switch ($k) {

                    case "entry":

                        switch ($v) {

                            case "password":

                                $post_request .= "Password = '" . $val . "'";
                                $valid = true;
                                break;

                            case "md5":

                                $post_request .= "MD5 = '" . $val . "'";
                                $valid = true;
                                break;

                            case "sha-1":

                                $post_request .= "`SHA-1` = '" . $val . "'";
                                $valid = true;
                                break;

                            case "sha-256":

                                $post_request .= "`SHA-256` = '" . $val . "'";
                                $valid = true;
                                break;

                            case "crc32":

                                $post_request .= "CRC32 = '" . $val . "'";
                                $valid = true;
                                break;

                            case "bcrypt":

                                $post_request .= "BCRYPT = '" . $val . "'";
                                $valid = true;
                                break;

                            case "id":

                                $post_request .= "ID = '" . $val . "'";
                                $valid = true;
                                break;

                            default:

                                if (!$valid) {

                                    $unknown_entry_var = true;

                                }
                                break;

                        }

                        break;

                    case "cs":
                        $case_sensitive = ($v == "true");
                        break;

                    case "action":
                        if ($v == "search" || $v == "view_all") $action_value = $v;
                        break;

                }

                $key = $k;
                $value = $v;

            }

            if ($unknown_entry_var && $action_value == "view_all") $unknown_entry_var = false;

            if (!$unknown_entry_var) {

                if ($action_value == "view_all") {

                    $request = "SELECT * FROM Passwords LIMIT 100";

                } else {

                    $request .= ($case_sensitive ? "BINARY " : "") . $post_request;

                }

                $result = $conn->query($request);
                $fields = array();

                $i = 0;

                $num_fields = $result->field_count;

                $a = 0;

                foreach ($result->fetch_fields() as $field) {

                    $fields_names[$a] = $field->name;
                    $fields[$field->name] = array();

                    $a++;

                }

                if ($result->num_rows > 0) {

                    while ($row = $result->fetch_assoc()) {

                        foreach ($fields_names as $name) {

                            $fields[$name][$i] = str_replace(" ", "□", $row[$name]);

                        }

                        $i++;

                    }

                } else {
                    $str[0] = "Cannot get data.";
                }

                $table = "<table>\n\n        <tr>\n\n";

                $id = 0;

                foreach ($fields_names as $name) {

                    $table .= "            <th class='col_" . $id . "'>" . $name . "</th>\n";
                    $id++;

                }

                $table .= "\n        </tr>";

                $u = 0;

                while ($u < $i) {

                    $add_str = "";

                    $id = 0;

                    foreach ($fields_names as $name) {

                        $add_str .= "\n\n            <th class='col_" . $id . "'><a href=https://duckduckgo.com/?q=";

                        if ($name != "Password") $add_str .= $name . "+";

                        $add_str .= $fields["Password"][$u] . ">" . $fields[$name][$u] . "</a></th>";

                        $id++;

                    }

                    $table .= "\n\n        <tr class='table_elements'>" . $add_str . "\n\n        </tr>";

                    $u++;

                }

                $table = $table . "\n\n    </table>\n";

                echo $table;
                echo "<!-- Query used : " . $request . "-->";

            } else {

                echo "Unknown entry.";

            }

        } else {

            $request = "SELECT `COLUMN_NAME` FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_SCHEMA`='Hashed_Passwords' AND `TABLE_NAME`='Passwords'";

            $result = $conn->query($request);
            $col_names = array();

            if ($result->num_rows > 0) {

                $i = 0;

                while ($row = $result->fetch_assoc()) {

                    $fields_names[$i] = $row["COLUMN_NAME"];
                    $i++;

                }

            }

            $request = "SELECT COUNT(*) AS `COUNT` FROM `Passwords` ";

            $result = $conn->query($request);
            $count = 0;

            if ($result->num_rows > 0) {

                while ($row = $result->fetch_assoc()) {

                    $count = $row["COUNT"];

                }

            }

            echo "Passwords Hashed : " . $count;

            $result = $conn->query("SELECT Password FROM Passwords ORDER BY ID DESC LIMIT 1");

            $pass = "";

            if ($result->num_rows > 0) {

                while ($row = $result->fetch_assoc()) {

                    $pass = $row["Password"];

                }

            }

            echo "<br>Last Password : " . $pass;
            echo "<script>document.title = \"" . $count . " Passwords\"</script>";

        }

        $conn->close();

        ?>

        <div id="form">

            <form action="index.php" method="get">

                <br><br>

                <input type="text" name="q" placeholder="Query"/>

                <br><br>

                Entry :
                <select name="entry">

                    <?php

                    foreach ($fields_names as $name) {

                        echo "<option value=\"" . strtolower($name) . "\">" . $name . "</option>\n            ";

                    }

                    ?>

                </select>

                <br><br>

                <label>Case sensitive :</label>
                <input type="checkbox" name="cs" value="true"/>

                <br><br>

                <button type="submit" name="action" value="search">Search</button>

            </form>

            <form action="index.php" method="get">

                <button type="submit" name="action" value="view_all">View all</button>

            </form>


        </div>

    </div>

    </body>

    </html>