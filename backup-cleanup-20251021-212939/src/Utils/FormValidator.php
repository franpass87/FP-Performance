<?php

namespace FP\PerfSuite\Utils;

use function __;
use function is_email;
use function filter_var;
use function in_array;
use function is_numeric;
use function sprintf;
use function trim;
use function array_key_exists;

use const FILTER_VALIDATE_URL;

/**
 * Form Validator Utility
 * 
 * Classe riusabile per validazione form consistente in tutto il plugin
 * Riduce codice duplicato e fornisce messaggi di errore uniformi
 * 
 * @package FP\PerfSuite\Utils
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 * 
 * @example
 * ```php
 * $validator = FormValidator::validate($_POST, [
 *     'cache_ttl' => ['required', 'numeric', 'min' => 60, 'max' => 86400],
 *     'email' => ['email'],
 *     'schedule' => ['required', 'in' => ['daily', 'weekly', 'monthly']],
 * ]);
 * 
 * if ($validator->fails()) {
 *     $message = $validator->firstError();
 * } else {
 *     $data = $validator->validated();
 *     // Salva dati
 * }
 * ```
 */
class FormValidator
{
    /**
     * @var array<string, mixed> Dati da validare
     */
    private array $data;
    
    /**
     * @var array<string, array<string>> Errori di validazione
     */
    private array $errors = [];
    
    /**
     * @var array<string, string> Etichette personalizzate per campi
     */
    private array $labels = [];
    
    /**
     * @param array<string, mixed> $data Dati da validare
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }
    
    /**
     * Metodo statico per validazione rapida
     * 
     * @param array<string, mixed> $data Dati da validare
     * @param array<string, array> $rules Regole di validazione
     * @param array<string, string> $labels Etichette personalizzate (opzionale)
     * @return self Istanza validator
     */
    public static function validate(array $data, array $rules, array $labels = []): self
    {
        $validator = new self($data);
        $validator->labels = $labels;
        
        foreach ($rules as $field => $fieldRules) {
            $validator->applyRules($field, $fieldRules);
        }
        
        return $validator;
    }
    
    /**
     * Applica regole di validazione a un campo
     * 
     * @param string $field Nome campo
     * @param array $rules Regole da applicare
     */
    private function applyRules(string $field, array $rules): void
    {
        foreach ($rules as $ruleKey => $ruleValue) {
            // Gestione regole con parametri (es. 'min' => 60)
            if (is_numeric($ruleKey)) {
                $rule = $ruleValue;
                $params = null;
            } else {
                $rule = $ruleKey;
                $params = $ruleValue;
            }
            
            $this->applyRule($field, $rule, $params);
        }
    }
    
    /**
     * Applica singola regola di validazione
     * 
     * @param string $field Nome campo
     * @param string $rule Nome regola
     * @param mixed $params Parametri regola
     */
    private function applyRule(string $field, string $rule, $params): void
    {
        $value = $this->getValue($field);
        $label = $this->getLabel($field);
        
        switch ($rule) {
            case 'required':
                if ($this->isEmpty($value)) {
                    $this->addError($field, sprintf(
                        __('Il campo %s è obbligatorio.', 'fp-performance-suite'),
                        $label
                    ));
                }
                break;
                
            case 'email':
                if (!$this->isEmpty($value) && !is_email($value)) {
                    $this->addError($field, sprintf(
                        __('Il campo %s deve contenere un\'email valida.', 'fp-performance-suite'),
                        $label
                    ));
                }
                break;
                
            case 'url':
                if (!$this->isEmpty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->addError($field, sprintf(
                        __('Il campo %s deve contenere un URL valido.', 'fp-performance-suite'),
                        $label
                    ));
                }
                break;
                
            case 'numeric':
                if (!$this->isEmpty($value) && !is_numeric($value)) {
                    $this->addError($field, sprintf(
                        __('Il campo %s deve essere un numero.', 'fp-performance-suite'),
                        $label
                    ));
                }
                break;
                
            case 'integer':
                if (!$this->isEmpty($value) && (!is_numeric($value) || (int)$value != $value)) {
                    $this->addError($field, sprintf(
                        __('Il campo %s deve essere un numero intero.', 'fp-performance-suite'),
                        $label
                    ));
                }
                break;
                
            case 'min':
                if (!$this->isEmpty($value) && is_numeric($value) && (float)$value < $params) {
                    $this->addError($field, sprintf(
                        __('Il campo %s deve essere almeno %s.', 'fp-performance-suite'),
                        $label,
                        $params
                    ));
                }
                break;
                
            case 'max':
                if (!$this->isEmpty($value) && is_numeric($value) && (float)$value > $params) {
                    $this->addError($field, sprintf(
                        __('Il campo %s non può superare %s.', 'fp-performance-suite'),
                        $label,
                        $params
                    ));
                }
                break;
                
            case 'min_length':
                if (!$this->isEmpty($value) && strlen($value) < $params) {
                    $this->addError($field, sprintf(
                        __('Il campo %s deve contenere almeno %d caratteri.', 'fp-performance-suite'),
                        $label,
                        $params
                    ));
                }
                break;
                
            case 'max_length':
                if (!$this->isEmpty($value) && strlen($value) > $params) {
                    $this->addError($field, sprintf(
                        __('Il campo %s non può superare %d caratteri.', 'fp-performance-suite'),
                        $label,
                        $params
                    ));
                }
                break;
                
            case 'in':
                if (!$this->isEmpty($value) && !in_array($value, (array)$params, true)) {
                    $this->addError($field, sprintf(
                        __('Il campo %s contiene un valore non valido.', 'fp-performance-suite'),
                        $label
                    ));
                }
                break;
                
            case 'not_in':
                if (!$this->isEmpty($value) && in_array($value, (array)$params, true)) {
                    $this->addError($field, sprintf(
                        __('Il campo %s contiene un valore non consentito.', 'fp-performance-suite'),
                        $label
                    ));
                }
                break;
                
            case 'boolean':
                if (!$this->isEmpty($value) && !is_bool($value) && !in_array($value, ['0', '1', 0, 1, true, false], true)) {
                    $this->addError($field, sprintf(
                        __('Il campo %s deve essere un valore booleano.', 'fp-performance-suite'),
                        $label
                    ));
                }
                break;
                
            case 'array':
                if (!$this->isEmpty($value) && !is_array($value)) {
                    $this->addError($field, sprintf(
                        __('Il campo %s deve essere un array.', 'fp-performance-suite'),
                        $label
                    ));
                }
                break;
                
            case 'regex':
                if (!$this->isEmpty($value) && !preg_match($params, $value)) {
                    $this->addError($field, sprintf(
                        __('Il campo %s non rispetta il formato richiesto.', 'fp-performance-suite'),
                        $label
                    ));
                }
                break;
                
            case 'confirmed':
                $confirmField = $field . '_confirmation';
                $confirmValue = $this->getValue($confirmField);
                if ($value !== $confirmValue) {
                    $this->addError($field, sprintf(
                        __('I campi %s non coincidono.', 'fp-performance-suite'),
                        $label
                    ));
                }
                break;
        }
    }
    
    /**
     * Ottiene valore di un campo
     * 
     * @param string $field Nome campo
     * @return mixed Valore campo o null
     */
    private function getValue(string $field)
    {
        return $this->data[$field] ?? null;
    }
    
    /**
     * Verifica se un valore è vuoto
     * 
     * @param mixed $value Valore da verificare
     * @return bool True se vuoto
     */
    private function isEmpty($value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        
        if (is_array($value) && empty($value)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Aggiunge errore di validazione
     * 
     * @param string $field Nome campo
     * @param string $message Messaggio errore
     */
    private function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        
        $this->errors[$field][] = $message;
    }
    
    /**
     * Ottiene etichetta campo (con fallback al nome)
     * 
     * @param string $field Nome campo
     * @return string Etichetta campo
     */
    private function getLabel(string $field): string
    {
        if (isset($this->labels[$field])) {
            return $this->labels[$field];
        }
        
        // Converti snake_case in Title Case
        return ucwords(str_replace('_', ' ', $field));
    }
    
    /**
     * Verifica se la validazione è fallita
     * 
     * @return bool True se ci sono errori
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }
    
    /**
     * Verifica se la validazione è passata
     * 
     * @return bool True se non ci sono errori
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }
    
    /**
     * Ottiene tutti gli errori
     * 
     * @return array<string, array<string>> Array di errori per campo
     */
    public function errors(): array
    {
        return $this->errors;
    }
    
    /**
     * Ottiene gli errori di un campo specifico
     * 
     * @param string $field Nome campo
     * @return array<string> Array di errori
     */
    public function errorsFor(string $field): array
    {
        return $this->errors[$field] ?? [];
    }
    
    /**
     * Ottiene il primo errore (di tutti i campi)
     * 
     * @return string|null Primo errore o null
     */
    public function firstError(): ?string
    {
        foreach ($this->errors as $fieldErrors) {
            if (!empty($fieldErrors[0])) {
                return $fieldErrors[0];
            }
        }
        return null;
    }
    
    /**
     * Ottiene il primo errore di un campo
     * 
     * @param string $field Nome campo
     * @return string|null Primo errore o null
     */
    public function firstErrorFor(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }
    
    /**
     * Ottiene i dati validati
     * 
     * @return array<string, mixed> Dati originali
     */
    public function validated(): array
    {
        return $this->data;
    }
    
    /**
     * Ottiene i dati validati solo per campi specifici
     * 
     * @param array<string> $fields Campi da includere
     * @return array<string, mixed> Subset di dati
     */
    public function only(array $fields): array
    {
        $result = [];
        foreach ($fields as $field) {
            if (array_key_exists($field, $this->data)) {
                $result[$field] = $this->data[$field];
            }
        }
        return $result;
    }
    
    /**
     * Ottiene i dati validati escludendo campi specifici
     * 
     * @param array<string> $fields Campi da escludere
     * @return array<string, mixed> Subset di dati
     */
    public function except(array $fields): array
    {
        $result = $this->data;
        foreach ($fields as $field) {
            unset($result[$field]);
        }
        return $result;
    }
    
    /**
     * Renderizza errori in formato HTML
     * 
     * @param string|null $field Campo specifico (null = tutti)
     * @return string HTML con errori
     */
    public function renderErrors(?string $field = null): string
    {
        $errors = $field ? $this->errorsFor($field) : $this->flatErrors();
        
        if (empty($errors)) {
            return '';
        }
        
        $html = '<div class="fp-ps-validation-errors" role="alert" aria-live="assertive">';
        $html .= '<ul style="margin: 0; padding-left: 20px; color: #dc2626;">';
        
        foreach ($errors as $error) {
            $html .= '<li>' . esc_html($error) . '</li>';
        }
        
        $html .= '</ul>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Ottiene array piatto di tutti gli errori
     * 
     * @return array<string> Array di tutti i messaggi di errore
     */
    private function flatErrors(): array
    {
        $flat = [];
        foreach ($this->errors as $fieldErrors) {
            foreach ($fieldErrors as $error) {
                $flat[] = $error;
            }
        }
        return $flat;
    }
    
    /**
     * Imposta etichette personalizzate per i campi
     * 
     * @param array<string, string> $labels Etichette
     * @return self Per method chaining
     */
    public function withLabels(array $labels): self
    {
        $this->labels = array_merge($this->labels, $labels);
        return $this;
    }
    
    /**
     * Aggiunge regola di validazione personalizzata
     * 
     * @param string $field Nome campo
     * @param callable $callback Funzione di validazione
     * @param string $message Messaggio di errore
     * @return self Per method chaining
     */
    public function addCustomRule(string $field, callable $callback, string $message): self
    {
        $value = $this->getValue($field);
        
        if (!$callback($value, $this->data)) {
            $this->addError($field, $message);
        }
        
        return $this;
    }
    
    /**
     * Ottiene il numero totale di errori
     * 
     * @return int Numero errori
     */
    public function errorCount(): int
    {
        return count($this->flatErrors());
    }
    
    /**
     * Verifica se un campo specifico ha errori
     * 
     * @param string $field Nome campo
     * @return bool True se ha errori
     */
    public function hasError(string $field): bool
    {
        return isset($this->errors[$field]) && !empty($this->errors[$field]);
    }
}

