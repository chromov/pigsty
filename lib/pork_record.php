<?php

class PorkRecord extends dbObject {

  private $translated_fields = array();

  public function translates($fields) {
    $this->translated_fields = $fields
  }

  private function __init() {
    if($this->databaseInfo->ID != false) {
			$fieldnames = implode(",", array_keys($this->databaseInfo->fields));
			$input = dbConnection::getInstance($this->databaseInfo->connection)->fetchRow("select {$fieldnames} from {$this->databaseInfo->table} where {$this->databaseInfo->primary} = {$this->databaseInfo->ID}", 'assoc');
			$this->import($input);
      if (sizeof($this->translated_fields > 0)) {
        $this->load_tranlations();
      }
		} 
  }

  private function load_tranlations() {
    $fieldnames = implode(",", array_keys($this->translated_fields));
    $values = dbConnection::getInstance($this->databaseInfo->connection)->fetchAll("select * from {$this->databaseInfo->table."_translations"} where parent_id = {$this->databaseInfo->ID}", 'assoc');

    if($values != false && sizeof($values) > 0) {
      foreach ($values as $row) {
        $translations[$row['locale']] = $row;
      }
      $locale = I18n::get_locale();
      while(($locale != "") && !isset($translations[$locale])) {
        $locale = I18n::fallback($locale);
      }
      $translated_fields = array();
      if ($locale != "") {
        $desired_row = $translations[$locale];
        foreach ($fieldnames as $field) {
          $translated_fields[$field] = $desired_row[$field]
        }
      }
      $this->databaseValues = array_merge($this->databaseValues, $translated_fields);
    }
  }

}

?>
