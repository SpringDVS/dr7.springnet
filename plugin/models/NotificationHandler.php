<?php

class NotificationHandler {

	private $table;
	private $totalPages;
	private $limit;
	public function __construct() {
		$this->table = 'sn_notifications';
		$this->limit = 10;
		$this->totalPages = null;
	}
	
	public function addNotification($title, $action, $source, $description) {
		
		return db_insert($this->table)
				->fields(array(
					'notif_title' => $title,
					'notif_action' => $action,
					'notif_source' => $source,
					'notif_description' => $description,
					'notif_active' => '0'
				))
				->execute();
		
		
		
	}
	
	public function getNotifications($paged = 1, $limit = 10) {
		$paged = $paged < 1 ? 1 : $paged;
		$this->limit = $limit;
		$from = ($paged-1) * $this->limit;
		
		if(!$this->totalPages) {
			$this->totalPages = ceil($this->getNotificationCount() / $limit);
		}
		return db_select($this->table, 'n')
					->fields('n')
					->condition('notif_active', 1, '=')
					->range($from, $limit)
					->orderBy('notif_id', 'DESC')
					->execute()
					->fetchAllAssoc('notif_id');
	}
	
	public function resolveNotificationId($id) {
		return db_delete($this->table)
						->condition('notif_id',$id,'=')
						->execute();
	}
	
	public function resolveNotificationAction($action) {
		return db_delete($this->table)
						->condition('notif_action',$action,'=')
						->execute();
	}
	
	public function activateNotification($id) {
		
		return db_update($this->table)
						->fields(array('notif_active'=>1))
						->condition('notif_id', $id,'=')
						->execute();
	}
	
	private function getNotificationCount() {
		return db_select($this->table, 'c')
					->fields('c',array('notif_id'))
					->countQuery()
					->execute()
					->fetchField();
	}
	
	public function getTotalPages() {
		if(!$this->totalPages) {
			$this->totalPages = ceil($this->getNotificationCount() / $this->limit);
		}
		return $this->totalPages;
	}
}