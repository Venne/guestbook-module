<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace GuestbookModule\Presenters;

use GuestbookModule\Forms\CommentFrontFormFactory;
use Nette\Application\ForbiddenRequestException;
use Nette\DateTime;
use Nette\Forms\Form;
use Venne;
use DoctrineModule\Repositories\BaseRepository;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class DefaultPresenter extends \CmsModule\Content\Presenters\PagePresenter
{

	/** @persistent */
	public $key;

	/** @var BaseRepository */
	protected $commentRepository;

	/** @var CommentFrontFormFactory */
	protected $commentFormFactory;


	public function __construct(BaseRepository $commentRepository)
	{
		parent::__construct();

		$this->commentRepository = $commentRepository;
	}


	/**
	 * @param \GuestbookModule\Forms\CommentFrontFormFactory $commentFormFactory
	 */
	public function injectCommentFormFactory(CommentFrontFormFactory $commentFormFactory)
	{
		$this->commentFormFactory = $commentFormFactory;
	}


	public function actionDefault()
	{
		if ($this->key) {
			$entity = $this->commentRepository->find($this->key);
			if (!$entity->author || !$this->user->isLoggedIn() || $entity->author->email !== $this->user->identity->getId()) {
				throw new ForbiddenRequestException;
			}
		}

		if ($this->isLoggedInAsSuperadmin()) {
			$this->flashMessage('You are logged in as superadmin. You can not send new comments.', 'info', true);
		}
	}


	public function handleEdit($id)
	{
		$this->key = $id;

		$this->redirect('this', array('key' => $id));
	}


	public function handleDelete($id)
	{
		$entity = $this->commentRepository->find($id);

		if ($entity->author && $this->user->isLoggedIn() && $entity->author->email === $this->user->identity->getId()) {
			$this->commentRepository->delete($entity);
		} else {
			throw new ForbiddenRequestException;
		}

		$this->flashMessage('Comment has been deleted.', 'success');
		$this->redirect('this');
	}


	public function getItemsBuilder()
	{
		return $this->getQueryBuilder()
			->setMaxResults($this->page->itemsPerPage)
			->setFirstResult($this['vp']->getPaginator()->getOffset());
	}


	/**
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	protected function getQueryBuilder()
	{
		return $this->commentRepository->createQueryBuilder("a")
			->andWhere('a.page = :page')->setParameter('page', $this->page->id);
	}


	protected function createComponentVp()
	{
		$vp = new \CmsModule\Components\VisualPaginator;
		$pg = $vp->getPaginator();
		$pg->setItemsPerPage($this->page->itemsPerPage);
		$pg->setItemCount($this->getQueryBuilder()->select("COUNT(a.id)")->getQuery()->getSingleScalarResult());
		return $vp;
	}


	protected function createComponentForm()
	{
		if ($this->isLoggedInAsSuperadmin()) {
			throw new ForbiddenRequestException;
		}

		$form = $this->commentFormFactory->invoke($this->commentRepository->createNew(array($this->page)));
		$form->onSuccess[] = $this->formSuccess;
		return $form;
	}


	public function formSuccess()
	{
		$this->flashMessage('Message has been saved.', 'success');
		$this->redirect('this');
	}


	protected function createComponentEditForm()
	{
		$entity = $this->commentRepository->find($this->key);

		$form = $this->commentFormFactory->invoke($entity);
		$form->onAttached[] = function (Form $form) {
			if ($form->isSubmitted()) {
				$form->data->updated = new DateTime;
			}
		};
		$form->onSuccess[] = $this->editFormSuccess;
		return $form;
	}


	public function editFormSuccess()
	{
		$this->flashMessage('Message has been updated.', 'success');
		$this->redirect('this', array('key' => NULL));
	}


	public function renderDefault()
	{
		$this->invalidateControl('guestbook');
	}


	/**
	 * @return bool
	 */
	public function isLoggedInAsSuperadmin()
	{
		return ($this->user->isLoggedIn() && $this->user->identity->getId() === $this->context->parameters['administration']['login']['name']);
	}
}