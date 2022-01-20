<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2020-2022
 * @package Client
 * @subpackage Html
 */


namespace Aimeos\Client\Html\Supplier\Detail;


/**
 * Default implementation of supplier detail section HTML clients.
 *
 * @package Client
 * @subpackage Html
 */
class Standard
	extends \Aimeos\Client\Html\Common\Client\Factory\Base
	implements \Aimeos\Client\Html\Common\Client\Factory\Iface
{
	/** client/html/supplier/detail/subparts
	 * List of HTML sub-clients rendered within the supplier detail section
	 *
	 * The output of the frontend is composed of the code generated by the HTML
	 * clients. Each HTML client can consist of serveral (or none) sub-clients
	 * that are responsible for rendering certain sub-parts of the output. The
	 * sub-clients can contain HTML clients themselves and therefore a
	 * hierarchical tree of HTML clients is composed. Each HTML client creates
	 * the output that is placed inside the container of its parent.
	 *
	 * At first, always the HTML code generated by the parent is printed, then
	 * the HTML code of its sub-clients. The order of the HTML sub-clients
	 * determines the order of the output of these sub-clients inside the parent
	 * container. If the configured list of clients is
	 *
	 *  array( "subclient1", "subclient2" )
	 *
	 * you can easily change the order of the output by reordering the subparts:
	 *
	 *  client/html/<clients>/subparts = array( "subclient1", "subclient2" )
	 *
	 * You can also remove one or more parts if they shouldn't be rendered:
	 *
	 *  client/html/<clients>/subparts = array( "subclient1" )
	 *
	 * As the clients only generates structural HTML, the layout defined via CSS
	 * should support adding, removing or reordering content by a fluid like
	 * design.
	 *
	 * @param array List of sub-client names
	 * @since 2020.10
	 * @category Developer
	 */
	private $subPartPath = 'client/html/supplier/detail/subparts';

	/** client/html/supplier/detail/navigator/name
	 * Name of the navigator part used by the supplier detail client implementation
	 *
	 * Use "Myname" if your class is named "\Aimeos\Client\Html\Supplier\Detail\Breadcrumb\Myname".
	 * The name is case-sensitive and you should avoid camel case names like "MyName".
	 *
	 * @param string Last part of the client class name
	 * @since 2014.09
	 * @category Developer
	 */
	private $subPartNames = [];

	private $tags = [];
	private $expire;
	private $view;


	/**
	 * Returns the HTML code for insertion into the body.
	 *
	 * @param string $uid Unique identifier for the output if the content is placed more than once on the same page
	 * @return string HTML code
	 */
	public function body( string $uid = '' ) : string
	{
		$prefixes = ['f_supid'];
		$context = $this->context();

		/** client/html/supplier/detail/cache
		 * Enables or disables caching only for the supplier detail component
		 *
		 * Disable caching for components can be useful if you would have too much
		 * entries to cache or if the component contains non-cacheable parts that
		 * can't be replaced using the modify() method.
		 *
		 * @param boolean True to enable caching, false to disable
		 * @category Developer
		 * @category User
		 * @see client/html/supplier/detail/cache
		 * @see client/html/supplier/filter/cache
		 * @see client/html/supplier/lists/cache
		 */

		/** client/html/supplier/detail
		 * All parameters defined for the supplier detail component and its subparts
		 *
		 * This returns all settings related to the detail component.
		 * Please refer to the single settings for details.
		 *
		 * @param array Associative list of name/value settings
		 * @category Developer
		 * @see client/html/supplier#detail
		 */
		$confkey = 'client/html/supplier/detail';

		if( ( $html = $this->getCached( 'body', $uid, $prefixes, $confkey ) ) === null )
		{
			$view = $this->view();

			/** client/html/supplier/detail/template-body
			 * Relative path to the HTML body template of the supplier detail client.
			 *
			 * The template file contains the HTML code and processing instructions
			 * to generate the result shown in the body of the frontend. The
			 * configuration string is the path to the template file relative
			 * to the templates directory (usually in client/html/templates).
			 *
			 * You can overwrite the template file configuration in extensions and
			 * provide alternative templates. These alternative templates should be
			 * named like the default one but with the string "standard" replaced by
			 * an unique name. You may use the name of your project for this. If
			 * you've implemented an alternative client class as well, "standard"
			 * should be replaced by the name of the new class.
			 *
			 * @param string Relative path to the template creating code for the HTML page body
			 * @since 2020.10
			 * @category Developer
			 * @see client/html/supplier/detail/template-header
			 */
			$tplconf = 'client/html/supplier/detail/template-body';
			$default = 'supplier/detail/body-standard';

			try
			{
				$html = '';

				if( !isset( $this->view ) ) {
					$view = $this->view = $this->object()->data( $view, $this->tags, $this->expire );
				}

				foreach( $this->getSubClients() as $subclient ) {
					$html .= $subclient->setView( $view )->body( $uid );
				}
				$view->detailBody = $html;

				$html = $view->render( $view->config( $tplconf, $default ) );
				$this->setCached( 'body', $uid, $prefixes, $confkey, $html, $this->tags, $this->expire );

				return $html;
			}
			catch( \Aimeos\Client\Html\Exception $e )
			{
				$error = array( $context->translate( 'client', $e->getMessage() ) );
				$view->detailErrorList = array_merge( $view->get( 'detailErrorList', [] ), $error );
			}
			catch( \Aimeos\Controller\Frontend\Exception $e )
			{
				$error = array( $context->translate( 'controller/frontend', $e->getMessage() ) );
				$view->detailErrorList = array_merge( $view->get( 'detailErrorList', [] ), $error );
			}
			catch( \Aimeos\MShop\Exception $e )
			{
				$error = array( $context->translate( 'mshop', $e->getMessage() ) );
				$view->detailErrorList = array_merge( $view->get( 'detailErrorList', [] ), $error );
			}
			catch( \Exception $e )
			{
				$error = array( $context->translate( 'client', 'A non-recoverable error occured' ) );
				$view->detailErrorList = array_merge( $view->get( 'detailErrorList', [] ), $error );
				$this->logException( $e );
			}

			$html = $view->render( $view->config( $tplconf, $default ) );
		}
		else
		{
			$html = $this->modify( $html, $uid );
		}

		return $html;
	}


	/**
	 * Returns the HTML string for insertion into the header.
	 *
	 * @param string $uid Unique identifier for the output if the content is placed more than once on the same page
	 * @return string|null String including HTML tags for the header on error
	 */
	public function header( string $uid = '' ) : ?string
	{
		$prefixes = ['f_supid'];
		$confkey = 'client/html/supplier/detail';

		if( ( $html = $this->getCached( 'header', $uid, $prefixes, $confkey ) ) === null )
		{
			$view = $this->view();

			/** client/html/supplier/detail/template-header
			 * Relative path to the HTML header template of the supplier detail client.
			 *
			 * The template file contains the HTML code and processing instructions
			 * to generate the HTML code that is inserted into the HTML page header
			 * of the rendered page in the frontend. The configuration string is the
			 * path to the template file relative to the templates directory (usually
			 * in client/html/templates).
			 *
			 * You can overwrite the template file configuration in extensions and
			 * provide alternative templates. These alternative templates should be
			 * named like the default one but with the string "standard" replaced by
			 * an unique name. You may use the name of your project for this. If
			 * you've implemented an alternative client class as well, "standard"
			 * should be replaced by the name of the new class.
			 *
			 * @param string Relative path to the template creating code for the HTML page head
			 * @since 2020.10
			 * @category Developer
			 * @see client/html/supplier/detail/template-body
			 */
			$tplconf = 'client/html/supplier/detail/template-header';
			$default = 'supplier/detail/header-standard';

			try
			{
				$html = '';

				if( !isset( $this->view ) ) {
					$view = $this->view = $this->object()->data( $view, $this->tags, $this->expire );
				}

				foreach( $this->getSubClients() as $subclient ) {
					$html .= $subclient->setView( $view )->header( $uid );
				}
				$view->detailHeader = $html;

				$html = $view->render( $view->config( $tplconf, $default ) );
				$this->setCached( 'header', $uid, $prefixes, $confkey, $html, $this->tags, $this->expire );

				return $html;
			}
			catch( \Exception $e )
			{
				$this->logException( $e );
			}
		}
		else
		{
			$html = $this->modify( $html, $uid );
		}

		return $html;
	}


	/**
	 * Returns the sub-client given by its name.
	 *
	 * @param string $type Name of the client type
	 * @param string|null $name Name of the sub-client (Default if null)
	 * @return \Aimeos\Client\Html\Iface Sub-client object
	 */
	public function getSubClient( string $type, string $name = null ) : \Aimeos\Client\Html\Iface
	{
		/** client/html/supplier/detail/decorators/excludes
		 * Excludes decorators added by the "common" option from the supplier detail html client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to remove a decorator added via
		 * "client/html/common/decorators/default" before they are wrapped
		 * around the html client.
		 *
		 *  client/html/supplier/detail/decorators/excludes = array( 'decorator1' )
		 *
		 * This would remove the decorator named "decorator1" from the list of
		 * common decorators ("\Aimeos\Client\Html\Common\Decorator\*") added via
		 * "client/html/common/decorators/default" to the html client.
		 *
		 * @param array List of decorator names
		 * @since 2020.10
		 * @category Developer
		 * @see client/html/common/decorators/default
		 * @see client/html/supplier/detail/decorators/global
		 * @see client/html/supplier/detail/decorators/local
		 */

		/** client/html/supplier/detail/decorators/global
		 * Adds a list of globally available decorators only to the supplier detail html client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap global decorators
		 * ("\Aimeos\Client\Html\Common\Decorator\*") around the html client.
		 *
		 *  client/html/supplier/detail/decorators/global = array( 'decorator1' )
		 *
		 * This would add the decorator named "decorator1" defined by
		 * "\Aimeos\Client\Html\Common\Decorator\Decorator1" only to the html client.
		 *
		 * @param array List of decorator names
		 * @since 2020.10
		 * @category Developer
		 * @see client/html/common/decorators/default
		 * @see client/html/supplier/detail/decorators/excludes
		 * @see client/html/supplier/detail/decorators/local
		 */

		/** client/html/supplier/detail/decorators/local
		 * Adds a list of local decorators only to the supplier detail html client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap local decorators
		 * ("\Aimeos\Client\Html\Supplier\Decorator\*") around the html client.
		 *
		 *  client/html/supplier/detail/decorators/local = array( 'decorator2' )
		 *
		 * This would add the decorator named "decorator2" defined by
		 * "\Aimeos\Client\Html\Supplier\Decorator\Decorator2" only to the html client.
		 *
		 * @param array List of decorator names
		 * @since 2020.10
		 * @category Developer
		 * @see client/html/common/decorators/default
		 * @see client/html/supplier/detail/decorators/excludes
		 * @see client/html/supplier/detail/decorators/global
		 */
		return $this->createSubClient( 'supplier/detail/' . $type, $name );
	}


	/**
	 * Processes the input, e.g. store given values.
	 *
	 * A view must be available and this method doesn't generate any output
	 * besides setting view variables if necessary.
	 */
	public function init()
	{
		$view = $this->view();
		$context = $this->context();

		try
		{
			parent::init();
		}
		catch( \Aimeos\Client\Html\Exception $e )
		{
			$error = array( $context->translate( 'client', $e->getMessage() ) );
			$view->detailErrorList = array_merge( $view->get( 'detailErrorList', [] ), $error );
		}
		catch( \Aimeos\Controller\Frontend\Exception $e )
		{
			$error = array( $context->translate( 'controller/frontend', $e->getMessage() ) );
			$view->detailErrorList = array_merge( $view->get( 'detailErrorList', [] ), $error );
		}
		catch( \Aimeos\MShop\Exception $e )
		{
			$error = array( $context->translate( 'mshop', $e->getMessage() ) );
			$view->detailErrorList = array_merge( $view->get( 'detailErrorList', [] ), $error );
		}
		catch( \Exception $e )
		{
			$error = array( $context->translate( 'client', 'A non-recoverable error occured' ) );
			$view->detailErrorList = array_merge( $view->get( 'detailErrorList', [] ), $error );
			$this->logException( $e );
		}
	}


	/**
	 * Returns the list of sub-client names configured for the client.
	 *
	 * @return array List of HTML client names
	 */
	protected function getSubClientNames() : array
	{
		return $this->context()->config()->get( $this->subPartPath, $this->subPartNames );
	}


	/**
	 * Sets the necessary parameter values in the view.
	 *
	 * @param \Aimeos\MW\View\Iface $view The view object which generates the HTML output
	 * @param array &$tags Result array for the list of tags that are associated to the output
	 * @param string|null &$expire Result variable for the expiration date of the output (null for no expiry)
	 * @return \Aimeos\MW\View\Iface Modified view object
	 */
	public function data( \Aimeos\MW\View\Iface $view, array &$tags = [], string &$expire = null ) : \Aimeos\MW\View\Iface
	{
		$context = $this->context();
		$config = $context->config();

		/** client/html/supplier/detail/supid-default
		 * The default supplier ID used if none is given as parameter
		 *
		 * You can configure the default supplier ID if no ID is passed in the
		 * URL using this configuration.
		 *
		 * @param string Supplier ID
		 * @since 2021.01
		 * @see client/html/catalog/lists/catid-default
		 */
		if( $supid = $view->param( 'f_supid', $config->get( 'client/html/supplier/detail/supid-default' ) ) )
		{
			$controller = \Aimeos\Controller\Frontend::create( $context, 'supplier' );

			/** client/html/supplier/detail/domains
			 * A list of domain names whose items should be available in the supplier detail view template
			 *
			 * The templates rendering the supplier detail section use the texts and
			 * maybe images and attributes associated to the categories. You can
			 * configure your own list of domains (attribute, media, price, product,
			 * text, etc. are domains) whose items are fetched from the storage.
			 * Please keep in mind that the more domains you add to the configuration,
			 * the more time is required for fetching the content!
			 *
			 * @param array List of domain names
			 * @since 2020.10
			 */
			$domains = $config->get( 'client/html/supplier/detail/domains', ['supplier/address', 'media', 'text'] );

			$supplier = $controller->uses( $domains )->get( $supid );

			$this->addMetaItems( $supplier, $expire, $tags );

			$view->detailSupplierItem = $supplier;
			$view->detailSupplierAddresses = $this->getAddressStrings( $view, $supplier->getAddressItems() );
		}

		return parent::data( $view, $tags, $expire );
	}


	/**
	 * Returns the addresses as list of strings
	 *
	 * @param \Aimeos\MW\View\Iface $view View object
	 * @param iterable $addresses List of address items implementing \Aimeos\MShop\Common\Item\Address\Iface
	 * @return \Aimeos\Map List of address strings
	 */
	protected function getAddressStrings( \Aimeos\MW\View\Iface $view, iterable $addresses ) : \Aimeos\Map
	{
		$list = [];

		foreach( $addresses as $id => $addr )
		{
			$list[$id] = preg_replace( "/\n+/m", "\n", trim( sprintf(
				/// Address format with company (%1$s), salutation (%2$s), title (%3$s), first name (%4$s), last name (%5$s),
				/// address part one (%6$s, e.g street), address part two (%7$s, e.g house number), address part three (%8$s, e.g additional information),
				/// postal/zip code (%9$s), city (%10$s), state (%11$s), country (%12$s), language (%13$s),
				/// e-mail (%14$s), phone (%15$s), facsimile/telefax (%16$s), web site (%17$s), vatid (%18$s)
				$view->translate( 'client', '%1$s
%2$s %3$s %4$s %5$s
%6$s %7$s
%8$s
%9$s %10$s
%11$s
%12$s
%13$s
%14$s
%15$s
%16$s
%17$s
%18$s
'
				),
				$addr->getCompany(),
				$view->translate( 'mshop/code', (string) $addr->getSalutation() ),
				$addr->getTitle(),
				$addr->getFirstName(),
				$addr->getLastName(),
				$addr->getAddress1(),
				$addr->getAddress2(),
				$addr->getAddress3(),
				$addr->getPostal(),
				$addr->getCity(),
				$addr->getState(),
				$view->translate( 'country', (string) $addr->getCountryId() ),
				$view->translate( 'language', (string) $addr->getLanguageId() ),
				$addr->getEmail(),
				$addr->getTelephone(),
				$addr->getTelefax(),
				$addr->getWebsite(),
				$addr->getVatID()
			) ) );
		}

		return map( $list );
	}
}
