<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace GuestbookModule\Components;

use Venne;
use CmsModule\Content\SectionControl;
use GuestbookModule\Forms\CommentFormFactory;
use DoctrineModule\Repositories\BaseRepository;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class TableControl extends SectionControl
{

	/** @var BaseRepository */
	protected $commentRepository;

	/** @var CommentFormFactory */
	protected $commentFormFactory;


	/**
	 * @param \DoctrineModule\Repositories\BaseRepository $commentRepository
	 * @param \BlogModule\Forms\CommentFormFactory $commentFormFactory
	 */
	public function __construct(BaseRepository $commentRepository, CommentFormFactory $commentFormFactory)
	{
		parent::__construct();

		$this->commentRepository = $commentRepository;
		$this->commentFormFactory = $commentFormFactory;
	}


	protected function createComponentTable()
	{
		$table = new \CmsModule\Components\Table\TableControl;
		$table->setTemplateConfigurator($this->templateConfigurator);
		$table->setRepository($this->commentRepository);

		$pageId = $this->entity->id;
		$table->setDql(function ($sql) use ($pageId) {
			$sql = $sql->andWhere('a.page = :page')->setParameter('page', $pageId);
			return $sql;
		});

		// forms
		$repository = $this->commentRepository;
		$entity = $this->entity;
		$form = $table->addForm($this->commentFormFactory, 'Comment', function () use ($repository, $entity) {
			return $repository->createNew(array($entity));
		}, \CmsModule\Components\Table\Form::TYPE_LARGE);

		// navbar
		$table->addButtonCreate('create', 'Create new', $form, 'file');

		$table->addColumn('text', 'Text')
			->setWidth('35%')
			->setSortable(TRUE)
			->setFilter();
		$table->addColumn('author', 'Author')
			->setWidth('25%')
			->setCallback(function ($entity) {
				return $entity->author ? $entity->author : $entity->authorName;
			});
		$table->addColumn('created', 'Created', \CmsModule\Components\Table\TableControl::TYPE_DATE_TIME)
			->setWidth('20%')
			->setSortable(TRUE);
		$table->addColumn('updated', 'Updated', \CmsModule\Components\Table\TableControl::TYPE_DATE_TIME)
			->setWidth('20%')
			->setSortable(TRUE);

		$table->addActionEdit('edit', 'Edit', $form);
		$table->addActionDelete('delete', 'Delete');

		// global actions
		$table->setGlobalAction($table['delete']);

		return $table;
	}


	public function render()
	{
		$this['table']->render();
	}
}
