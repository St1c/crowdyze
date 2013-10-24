<?php

class SomePresenter
{

	// Následující konstrukci využívám k líné inicializaci modelu
	// a udržování jen jedné instance ve všech koutech Presenteru
	private $detailModel;

	/**
	 * @return Detail
	 */
	public function getDetailModel()
	{
		if ($this->detailModel === NULL)
			$this->detailModel = new Detail;
		return $this->detailModel;
	}

	// Proměnná, která určuje, zda zobrazíme formulář,
	// případně pro kterou položku
	protected $editTitle = FALSE;

	public function renderAction(/* ... */)
	{
		// Zde proběhne získání záznamu, popř. záznamů,
		// pokud jich je na stránce víc

		$this->template->editTitle = $this->editTitle;
	}


	public function createComponentEditTitleForm()
	{
		$form = new Form();
		// Formuláři nastavíme třídu ajax
		$form->getElementPrototype()->class('ajax');
		$form->addText('nazev', 'Název:');
		$form->addHidden('id');
		$form->addSubmit('ulozit', 'Uložit');
		$form->addSubmit('zrusit', 'Zrušit');
		// Funkce, která se zavolá po odeslání formuláře
		$form->onSubmit[] = array($this, 'EditTitleForm_Submit');

		/* Pokud používáš AJAXové odesílání formuláře, zkontroluj,
		zda je v JavaScriptu nastavené odesílání formuláře i na submit:
		$("form.ajax :submit").livequery("click", function () {
			$(this).ajaxSubmit();
			return false;
		});
		(vyžaduje plugin livequery, ale pokud používáš DataGrid,
		měl by být k dispozici)
		*/

	return $form;
	}

	public function EditTitleForm_Submit(Form $form)
	{
		$values = $form->getValues();
		if ($form['ulozit']->isSubmittedBy()) {
			$this->getDetailModel()->update(
				$values['id'],
				array(
					'nazev' => $values['nazev']
					)
				);
				/* Pokud používáš DibiTableX, můžeš použít variantu
				$this->getDetailModel()->update(
					NULL,
					$form->getValues()
				);
				*/
		}

		// Při ajaxu nepřesměrováváme, ale invalidujeme
		if (!$this->isAjax())
			$this->redirect('this');
		else {
					// Překreslíme jen jeden snippet, ne všechny
			$this->validateControl();
			$this->invalidateControl('title' . $values['id']);
		}
	}

	public function handleEditTitle($editId)
	{
		$this->editTitle = $editId;

		$row = $this->getDetailModel()->findDetail($id)->fetch();
		if ($row === FALSE)
			throw new BadRequestException('Záznam nebyl nalezen.');
		$this->getComponent('EditTitleForm')
		->setDefaults(array(
			'id' => $row->id,
			'nazev' => $row->nazev
			));

			// Překreslíme jen jeden snippet, ne všechny
		$this->validateControl();
		$this->invalidateControl('title' . $editId);
	}

	// ...
}