<?php

require_once($CFG->libdir . '/questionlib.php');

class local_qpreviewjson {
	protected $questionid;
	protected $quba;

	public function __construct($questionid, $qubaid = null) {
		$this->questionid = $questionid;
		if ($qubaid) {
			$this->load_quba($qubaid);
		}
	}

	protected function load_quba($qubaid) {
		$this->quba = question_engine::load_questions_usage_by_activity($qubaid);
		if ($this->quba->get_question(1)->id != $this->questionid) {
			throw new coding_exception('questionid mismatch');
		}
	}

	public function get_current_state() {
		$result = array(
			'question_id' => $this->questionid,
			'quba_id' => $this->quba->get_id(),
			'question' => $this->quba->get_question(1),
			'current_state' => $this->get_current_question_state(),
			'steps' => $this->get_question_steps(),
		);
		return $result;
	}

	public function get_current_question_state() {
		$qa = $this->quba->get_question_attempt(1);
		return array(
			'behaviour' => $qa->get_behaviour_name(),
			'is_flagged' => $qa->is_flagged(),
			'previx' => $qa->get_field_prefix(),
			'max_mark' => $qa->get_max_mark(),
			'min_fraction' => $qa->get_min_fraction(),
			'question_summary' => $qa->get_question_summary(),
			'response_summary' => $qa->get_response_summary(),
		);
	}

	public function get_question_steps() {
		$steps = array();
		foreach ($this->quba->get_question_attempt(1)->get_step_iterator() as $step) {
			$steps[] = array(
				'state' => $step->get_state(),
				'fraction' => $step->get_fraction(),
				'user_id' => $step->get_user_id(),
				'timecreated' => $step->get_timecreated(),
				'data' => $step->get_all_data(),
			);
		}
		return $steps;		
	}

	public function start_preview($behaviour = 'deferredfeedback') {
		global $DB, $PAGE;
		
		$context = get_context_instance(CONTEXT_SYSTEM); //TODO
		$PAGE->set_context($context);
		$question = question_bank::load_question($this->questionid);

	    $quba = question_engine::make_questions_usage_by_activity(
	            'core_question_preview', $context);
	    $quba->set_preferred_behaviour($behaviour);
	    $slot = $quba->add_question($question, $question->defaultmark);
	
	    $quba->start_question($slot);
	
	    $transaction = $DB->start_delegated_transaction();
	    question_engine::save_questions_usage_by_activity($quba);
	    $transaction->allow_commit();

	    $this->quba = $quba;
	}

	public function process_submission() {
        $this->quba->process_all_actions();

        $transaction = $DB->start_delegated_transaction();
        question_engine::save_questions_usage_by_activity($quba);
        $transaction->allow_commit();
	}

	public function finish_preview() {
		global $DB;

		$this->quba->process_all_actions();
        $this->quba->finish_all_questions();

        $transaction = $DB->start_delegated_transaction();
        question_engine::save_questions_usage_by_activity($quba);
        $transaction->allow_commit();
	}
}
