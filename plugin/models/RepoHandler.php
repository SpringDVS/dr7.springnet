<?php
class RepoHandler {
	private $table;
	public function __construct(){
		$this->table = 'sn_repo';
	}
	
	public function getDataFromTag($tag) {
		return db_select($this->table, 'r')
					->fields('r')
					->condition('repo_tag', $tag, '=')
					->execute()
					->fetchAllAssoc('repo_id');
	}
	
	public function getDatumFromId($tag, $id) {
		
		$rows = db_select($this->table, 'r')
					->fields('r')
					->condition('repo_tag', $tag, '=')
					->condition('repo_id', $id, '=')
					->execute()
					->fetchAllAssoc('repo_id');

		if(!$rows || empty($rows)) {
			return null;
		}

		return reset($rows);
	}
	
	public function addData($tag, $data, $notification = 0) {
		return db_insert($this->table)
							->fields(array(
								'repo_tag' => $tag,
								'repo_timestamp' => date("Y-m-d H:i:s", time()),
								'repo_notif' => $notification,
								'repo_data' => $data,
							))
							->execute();
	}
	
	public function removeDataWithId($tag, $id) {
		return db_delete($this->table)
					->condition('repo_id', $id, '=')
					->condition('repo_tag', $tag, '=')
					->execute();
	}
	
	public function dataExists($tag, $data) {
		
		$v = db_select($this->table, 'r')
						->fields('r', array('repo_id'))
						->condition('repo_tag', $tag,'=')
						->condition('repo_data', $data, '=')
						->execute()
						->fetchField();
		
		if(!$v) return false;
		
		return true;		
	}
	
}