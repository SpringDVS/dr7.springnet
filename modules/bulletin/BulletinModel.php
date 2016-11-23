<?php
class BulletinModel {
	private $vid;

	public function __construct() {
		$vm = taxonomy_vocabulary_machine_name_load('tags');
		if(!$vm) { 
			$this->vid = 0;
			return;
		}
		$this->vid = $vm->vid;
	}

	public function withUid($uid) {
		
		$node = node_load($uid);
		if($node->type != 'snbulletin'){
			return array();
		}
		return $this->bulletinFromNode($node);
	}

	public function withFilters($categories, $tags, $limit = 5) {

		if(empty($categories) && empty($tags)) {
			return $this->emptyFilters($limit);
		}
		$q = null;
		
	
		// Build a dynamic query based on filters
		if(!empty($categories)) {
			
			// There is a filter of categories
			$q = db_select('field_data_field_snbulletin_category', 'cat');
			$q->fields('cat', array('entity_id'));
			
			if(!empty($tags)) {

				// There is also a filter of tags, so we need to join
				$q->join('field_data_field_tags', 'tag', 'cat.entity_id = tag.entity_id');
				
				$tids = $this->termIds($tags);
				if(empty($tids)){ return array(); } // No tags exist for filter
				
				$or = db_or();
				foreach($tids as $term) {
					$or->condition('tag.field_tags_tid', $term->tid, '=');
				}

				$q->condition($or);
			}

			// Now sort out the categories
			$tids = $this->termIds($categories);			
			if(empty($tids)){ return array(); } // No cats exist for filter 
			
			$orc = db_or();
			foreach($tids as $term) {
				$orc->condition('field_snbulletin_category_tid', $term->tid, '=');
			}
			$q->condition($orc);
			
				
		} else {

			// There are only tags to filter through
			$q = db_select('field_data_field_tags', 'tag')
							->fields('tag', array('entity_id'));
			
			$tids = $this->termIds($tags);
			if(empty($tids)){ return array(); } // No tags exist for filter
			
			$or = db_or();
			
			foreach($this->termIds($tags) as $term) {
				$or->condition('tag.field_tags_tid', $term->tid, '=');
			}	
			
			
		}
		
		$q->range(0, $limit);
		$q->orderBy('entity_id', 'DESC');
		$ids = $q->execute()->fetchAllAssoc('entity_id');
		$eids = array();
		foreach($ids as $id) {
			$eids[] = $id->entity_id;
		}
		$nodes = node_load_multiple($eids);
		 
		return $this->bulletinFromNodes($nodes);
	}
	
	private function emptyFilters($limit = 5) {
		$eq = new EntityFieldQuery();
		$eq->entityCondition('entity_type', 'node')
			->entityCondition('bundle', 'snbulletin')
			->range(0,$limit)
			->propertyOrderBy('nid', 'DESC');

		$set = $eq->execute();
		if(!$set || !isset($set['node']) ){ return array(); }
		
		$ids = array();
		foreach($set['node'] as $s) {
			$ids[] = $s->nid;
		}
		

		$nodes = node_load_multiple($ids);
		return $this->bulletinFromNodes($nodes);
	}
	
	private function bulletinFromNodes($nodes) {
		$out = array();
		foreach($nodes as $node) {
			$out[] = $this->bulletinFromNode($node);
		}
		
		return $out;
	}

	private function bulletinFromNode($node) {
		$out = array();
		
		$out['title'] = $node->title;
		
		$tmp = reset($node->body);
		$out['content'] = $tmp[0]['value'];
		$out['uid'] = $node->nid;
		$out['tags'] = $this->tagsFromNode($node);
		return $out;
	}

	private function tagsFromNode($node) {
		$tags = array();
		$listing = reset($node->field_tags);
		$tids = array();
		foreach($listing as $tag) {
			$tids[] = $tag['tid'];
		}
		
		foreach(taxonomy_term_load_multiple($tids) as $tag) {
			$tags[] = $tag->name;
		}

		return $tags;
	}
	
	private function termIds(array $categories) {
		$or = db_or();
		foreach($categories as $c) {
			$or->condition('name', $c, '=');
		}
		return db_select('taxonomy_term_data', 't')
				->fields('t', array('tid','name'))
				->condition('vid', $this->vid, '=')
				->condition($or)
				->execute()
				->fetchAllAssoc('tid');
	}	
}