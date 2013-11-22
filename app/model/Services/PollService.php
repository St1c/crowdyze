<?php
namespace Model\Services;

use Nette,
	Model\Repositories,
	Utilities,
	Nette\Database\Table\ActiveRow,
	Nette\Http\FileUpload,
	Nette\Utils\Strings;

class PollService extends Nette\Object
{

	/** @var pollRepository */
	private $pollRepository;
	/** @var emailRepository */
	private $emailRepository;
	/** @var questionRepository */
	private $questionRepository;
	/** @var session */
	private $session;



	public function __construct(Repositories\PollRepository $pollRepository,
								Repositories\EmailRepository $emailRepository,
								Repositories\QuestionRepository $questionRepository)
	{
		$this->emailRepository 		= $emailRepository;
		$this->pollRepository 		= $pollRepository;
		$this->questionRepository 	= $questionRepository;
	}


	public function createNewPoll()
	{

		// $formSession = $this->session->getSection('formSession');
		// if (!isset($formSession->uuid)) {
		// 	$formSession->uuid = uniqid();
		// }

		// $pollInDb = $this->pollRepository->find($formSession->uuid);

		// if ($pollInDb) {
		// 	return $pollInDb;			
		// } else {
		// 	return  $this->pollRepository->create(array(
		// 		'uuid' => $formSession->uuid
		// 	));
		// }

		return  $this->pollRepository->create(array(
			'uuid' => uniqid()
		));
	}

	/**
	 * Get single task
	 * 
	 * @param int task ID
	 * @return ActiveRow
	 */
	public function addAnswer($poll, $tag, $value)
	{
		$question = $this->getQuestionByTag($tag);

		if (!is_null($question->id)){
			return $poll->related('answer.poll_id')->insert(array(
				'question_id' 	=> $question->id,
				'answer'		=> $value
			));
		} else {
			throw new \Exception("Answer to non-existing question", 1);
		}

	}

	private function getQuestionByTag($tag)
	{
		return $this->questionRepository->getByTag($tag);
	}

	private function registerToNewsletter($poll, $email)
	{

	}

	public function assignEmailToPoll($pollId, $form)
	{

		$email = $this->emailRepository->find($form->email);

		if (!$email) {
			$email = $this->emailRepository->create(array(
				'email' 		=> $form->email,
				'subscribe' 	=> $form->subscribe
			));
		}

		$poll = $this->pollRepository->find($pollId);

		if ($poll) {
			
			$poll->update(array(
				'email_id' => $email->id
			));

		} else {
			throw new \Exception("Can't assign email to the poll.", 1);
			
		}
	}
}