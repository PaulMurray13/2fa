<?php
/**
 * Clase de conexión PDO
 * Usuario de BD: app_lab2fa (privilegios mínimos - NO superusuario)
 */
class mod_db
{
    private $conexion;
    private $debug = false;

    public function __construct()
    {
        $sql_host = "localhost";
        $sql_name = "company_info";
        $sql_user = "app_lab2fa";      // Usuario con privilegios mínimos
        $sql_pass = "Lab2FA_2026#Secure";

        $dsn = "mysql:host=$sql_host;dbname=$sql_name;charset=utf8mb4";
        try {
            $this->conexion = new PDO($dsn, $sql_user, $sql_pass);
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conexion->exec("SET NAMES utf8mb4");
            if ($this->debug) {
                echo "Conexión exitosa a la base de datos<br>";
            }
        } catch (PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
            exit;
        }
    }

    public function getConexion()
    {
        return $this->conexion;
    }

    public function disconnect()
    {
        $this->conexion = null;
    }

    public function insert($tb_name, $cols, $val)
    {
        $cols = $cols ? "($cols)" : "";
        $sql  = "INSERT INTO $tb_name $cols VALUES ($val)";
        try {
            $this->conexion->exec($sql);
        } catch (PDOException $e) {
            echo "Error al insertar: " . $e->getMessage();
        }
    }

    public function insertSeguro($tb_name, $data)
    {
        $columns      = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));
        $sql          = "INSERT INTO $tb_name ($columns) VALUES ($placeholders)";
        try {
            $stmt = $this->conexion->prepare($sql);
            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            echo "Error en INSERT: " . $e->getMessage();
            return false;
        }
    }

    public function update($tb_name, $string, $astriction)
    {
        $sql = "UPDATE $tb_name SET $string WHERE $astriction";
        try {
            $this->conexion->exec($sql);
        } catch (PDOException $e) {
            echo "Error al Modificar: " . $e->getMessage();
        }
    }

    public function updateSeguro($tabla, $data, $condiciones)
    {
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "$key = :$key";
        }
        $setSQL = implode(", ", $set);

        $where = [];
        foreach ($condiciones as $key => $value) {
            $where[] = "$key = :cond_$key";
        }
        $whereSQL = implode(" AND ", $where);
        $sql      = "UPDATE $tabla SET $setSQL WHERE $whereSQL";

        try {
            $stmt = $this->conexion->prepare($sql);
            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            foreach ($condiciones as $key => $value) {
                $stmt->bindValue(":cond_$key", $value);
            }
            return $stmt->execute();
        } catch (PDOException $e) {
            echo "Error en UPDATE: " . $e->getMessage();
            return false;
        }
    }

    public function del($tb_name, $astriction)
    {
        $sql = "DELETE FROM $tb_name";
        if ($astriction) {
            $sql .= " WHERE $astriction";
        }
        $this->executeQuery($sql);
    }

    public function log($Usuario)
    {
        try {
            $sql  = "SELECT * FROM usuarios WHERE Usuario = :User";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':User', $Usuario, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchObject();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }

    public function executeQuery($string)
    {
        try {
            $stmt = $this->conexion->prepare($string);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }

    public function nums($string = "", $stmt = null)
    {
        if ($string) {
            $stmt = $this->executeQuery($string);
        }
        if ($stmt != null) {
            $this->total = $stmt ? $stmt->rowCount() : 0;
            return $this->total;
        }
        return 0;
    }

    public function objects($stmt = "")
    {
        return $stmt ? $stmt->fetch(PDO::FETCH_OBJ) : null;
    }

    public function Arreglos($string = "")
    {
        try {
            if ($string) {
                $stmt = $this->conexion->query($string);
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        return isset($stmt) && $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function insert_id()
    {
        return $this->conexion->lastInsertId(); // CORREGIDO: era $this->Conexion
    }
}
