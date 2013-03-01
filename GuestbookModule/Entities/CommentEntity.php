<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace GuestbookModule\Entities;

use Venne;
use Doctrine\ORM\Mapping as ORM;
use CmsModule\Security\Entities\UserEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity(repositoryClass="\GuestbookModule\Repositories\CommentRepository")
 * @ORM\Table(name="guestbook_comment")
 */
class CommentEntity extends \DoctrineModule\Entities\IdentifiedEntity
{
	/**
	 * @var string
	 * @ORM\Column(type="text")
	 */
	protected $text = '';

	/**
	 * @var PageEntity
	 * @ORM\ManyToOne(targetEntity="\GuestbookModule\Entities\PageEntity", inversedBy="comments")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $page;

	/**
	 * @var \CmsModule\Security\Entities\UserEntity
	 * @ORM\ManyToOne(targetEntity="\CmsModule\Security\Entities\UserEntity")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $author;

	/**
	 * @var string
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $authorName = '';

	/**
	 * @var \DateTime
	 * @ORM\Column(type="datetime")
	 */
	protected $created;

	/**
	 * @var \DateTime
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $updated;


	/**
	 * @param PageEntity $pageEntity
	 */
	public function __construct(PageEntity $pageEntity)
	{
		parent::__construct();

		$this->page = $pageEntity;
		$this->created = new \Nette\DateTime();
	}


	/**
	 * @param \CmsModule\Security\Entities\UserEntity $author
	 */
	public function setAuthor($author)
	{
		if ($author instanceof UserEntity) {
			$this->author = $author;
			$this->authorName = NULL;
			return;
		} else if (is_string($author) && $author) {
			$this->authorName = $author;
			$this->author = NULL;
			return;
		}
	}


	/**
	 * @return \CmsModule\Security\Entities\UserEntity
	 */
	public function getAuthor()
	{
		return $this->author;
	}


	/**
	 * @param string $authorName
	 */
	public function setAuthorName($authorName)
	{
		$this->authorName = $authorName;

		if ($authorName) {
			$this->author = NULL;
		}
	}


	/**
	 * @return string
	 */
	public function getAuthorName()
	{
		return $this->authorName;
	}


	/**
	 * @param \DateTime $created
	 */
	public function setCreated($created)
	{
		$this->created = $created;
	}


	/**
	 * @return \DateTime
	 */
	public function getCreated()
	{
		return $this->created;
	}


	/**
	 * @param \GuestbookModule\Entities\PageEntity $page
	 */
	public function setPage($page)
	{
		$this->page = $page;
	}


	/**
	 * @return \GuestbookModule\Entities\PageEntity
	 */
	public function getPage()
	{
		return $this->page;
	}


	/**
	 * @param string $text
	 */
	public function setText($text)
	{
		$this->text = $text;
	}


	/**
	 * @return string
	 */
	public function getText()
	{
		return $this->text;
	}


	/**
	 * @param \DateTime $updated
	 */
	public function setUpdated($updated)
	{
		$this->updated = $updated;
	}


	/**
	 * @return \DateTime
	 */
	public function getUpdated()
	{
		return $this->updated;
	}
}
