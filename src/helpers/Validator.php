<?php
/**
 * Clase Helper para Validación de Datos
 */

class Validator {
    
    /**
     * Valida un email
     */
    public static function email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Valida que un campo no esté vacío
     */
    public static function required($value) {
        return !empty(trim($value));
    }
    
    /**
     * Valida longitud mínima
     */
    public static function minLength($value, $min) {
        return strlen(trim($value)) >= $min;
    }
    
    /**
     * Valida longitud máxima
     */
    public static function maxLength($value, $max) {
        return strlen(trim($value)) <= $max;
    }
    
    /**
     * Valida que sea un número
     */
    public static function numeric($value) {
        return is_numeric($value);
    }
    
    /**
     * Valida que sea un entero
     */
    public static function integer($value) {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }
    
    /**
     * Sanitiza string
     */
    public static function sanitizeString($value) {
        return filter_var($value, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
    }
    
    /**
     * Sanitiza email
     */
    public static function sanitizeEmail($value) {
        return filter_var($value, FILTER_SANITIZE_EMAIL);
    }
    
    /**
     * Valida múltiples campos según reglas
     */
    public static function validate($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            
            foreach ($fieldRules as $rule => $param) {
                if ($rule === 'required' && !self::required($value)) {
                    $errors[$field][] = "El campo {$field} es requerido";
                    break;
                }
                
                if ($rule === 'email' && !self::email($value)) {
                    $errors[$field][] = "El campo {$field} debe ser un email válido";
                }
                
                if ($rule === 'min' && !self::minLength($value, $param)) {
                    $errors[$field][] = "El campo {$field} debe tener al menos {$param} caracteres";
                }
                
                if ($rule === 'max' && !self::maxLength($value, $param)) {
                    $errors[$field][] = "El campo {$field} no puede tener más de {$param} caracteres";
                }
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}

