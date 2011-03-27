<?php

/**
 * @author Michael "beefsack" Alexander
 * @license http://www.gnu.org/licenses/gpl.html GPL
 * @version 1.0
 * @link http://beefsack.com
 */

namespace TheTVDB;

/**
 * The adapter class, which is the main interface.
 */
class Adapter
{

    /**
     * The key for this adapter
     * @var string
     * @see http://thetvdb.com/?tab=apiregister
     */
    protected $_key;


	/**
	 * The language to fetch results in
	 * @var string
	 */
	protected $_language = 'english';


    /**
     * Sets the key for this adapter
     * @param string $key
     * @return Adapter
     * @see http://thetvdb.com/?tab=apiregister
     */
    public function setKey($key)
    {

        $this->_key = $key;

        return $this;

    }


    /**
     * Gets the key for this adapter
     * @return string
     * @see http://thetvdb.com/?tab=apiregister
     */
    public function getKey()
    {

        return $this->_key;

    }


	/**
	 * Sets the language to fetch results in
	 * @param string $language
	 * @return Adapter
	 */
	public function setLanguage($language)
	{

		$this->_language = $language;

		return $this;

	}


	/**
	 * Gets the language to fetch results in
	 * @return string
	 */
	public function getLanguage()
	{

		return $this->_language;

	}


	public function getSeries($id)
	{

		$series = new Series($this);

		return $series->load($id);

	}


}


class Request
{

	const BASE_URL = '{{{mirror}}}/api/{{{key}}}/{{{page}}}';


	/**
	 * The page that will be requested
	 * @var
	 */
	protected $_page;


	/**
	 * The response from the last request
	 * @var string
	 */
	protected $_response;


	/**
	 * The params for the request
	 * @var array
	 */
	protected $_params = array();


	/**
	 * The available mirrors
	 * @var array
	 */
	protected static $_mirrors;


	/**
	 * The mirror to make the request to
	 * @var string
	 */
	protected $_mirror = 'http://thetvdb.com';


    /**
     * The key for this adapter
     * @var string
     * @see http://thetvdb.com/?tab=apiregister
     */
    protected $_key;


    /**
     * Sets the key for this adapter
     * @param string $key
     * @return Adapter
     * @see http://thetvdb.com/?tab=apiregister
     */
    public function setKey($key)
    {

        $this->_key = $key;

        return $this;

    }


    /**
     * Gets the key for this adapter
     * @return string
     * @see http://thetvdb.com/?tab=apiregister
     */
    public function getKey()
    {

        return $this->_key;

    }


	public function setPage($page)
	{

		$this->_page = $page;

		return $this;

	}


	public function getPage()
	{

		return $this->_page;

	}


	public function setParam($name, $value)
	{

		$this->_params[$name] = $value;

		return $this;

	}


	public function getParam($name)
	{

		return $this->_params[$name];

	}


	public function setParams(array $params)
	{

		$this->_params = $params;

		return $this;

	}


	public function getParams()
	{

		return $this->_params;

	}


	public function setMirror($mirror)
	{

		$this->_mirror = $mirror;

		return $this;

	}


	public function getMirror()
	{

		return $this->_mirror;

	}


	public function findMirror($type)
	{

		return $this->setMirror($this->getRandomMirror($type));

	}


	public function getMirrors()
	{

		if (self::$_mirrors === null)
			self::$_mirrors = $this->parseMirrorListXml(
					$this->requestMirrorList());

		return self::$_mirrors;

	}


	public function getRandomMirror($type)
	{

		$mirrors = $this->getMirrors();

		return $mirrors[$type][array_rand($mirrors[$type])];

	}


	protected function requestMirrorList()
	{

		$request = new self();
		$response = $request->setPage('mirrors.xml')
				->setKey($this->getKey())
				->request()
				->getResponse();

		return $response;

	}


	protected function parseMirrorListXml($xml)
	{

		$mirrors = array(
			'xml' => array(),
			'banner' => array(),
			'zip' => array(),
		);

		$data = simplexml_load_string($xml);

		foreach ($data as $mirror) {

			$path = (string) $mirror->mirrorpath;
			$typemask = (int) $mirror->typemask;

			if ($typemask & 1)
				$mirrors['xml'][] = $path;

			if ($typemask & 2)
				$mirrors['banner'][] = $path;

			if ($typemask & 4)
				$mirrors['zip'][] = $path;

		}

		return $mirrors;

	}


	/**
	 * Gets the escaped query string
	 * @return string
	 */
	protected function getQueryString()
	{

		$params = array();

		foreach ($this->_params as $key => $value) {

			$params = urlencode($key) . '=' . urlencode($value);

		}

		return implode('&', $params);

	}


	public function request()
	{

		$ch = curl_init($this->getUrl());
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$this->_response = curl_exec($ch);
		curl_close($ch);

		return $this;
		
	}


	public function getResponse()
	{

		return $this->_response;

	}


	public function getUrl()
	{

		$url = self::BASE_URL;
		$url = str_replace('{{{mirror}}}', $this->getMirror(), $url);
		$url = str_replace('{{{key}}}', $this->getKey(), $url);
		$url = str_replace('{{{page}}}', $this->getPage(), $url);

		$query = $this->getQueryString();

		if ($query)
			$url .= "?$query";

		return $url;

	}


}


class Data implements \ArrayAccess
{

	protected $_data = array();


	/**
	 * The adapter for this data
	 * @var Adapter
	 */
	protected $_adapter;


	public function __construct(namespace\Adapter $adapter)
	{

		$this->setAdapter($adapter);

	}


	/**
	 * Sets the adapter for this data
	 * @param Adapter $adapter
	 * @return Data
	 */
	public function setAdapter(namespace\Adapter $adapter)
	{

		$this->_adapter = $adapter;

		return $this;

	}


	/**
	 * Gets the adapter for this data
	 * @return Adapter
	 */
	public function getAdapter()
	{

		return $this->_adapter;

	}


	public function toArray()
	{

		return $this->_data;

	}


	public function offsetGet($offset)
	{

		return $this->_data[$offset];

	}


	public function offsetExists($offset)
	{

		return $this->_data[$offset] !== null;

	}


	public function offsetSet($offset, $value)
	{

		$this->_data[$offset] = $value;

	}


	public function offsetUnset($offset)
	{

		unset($this->_data[$offset]);

	}


	protected function _explodeBarDelimitedString($string)
	{

		$str = trim($string, '|');

		if (!$str) return array();

		return explode('|', $str);

	}


}


class Series extends Data
{

	public function load($id)
	{

		$request = new Request();
		$this->_loadXml($request->setKey($this->getAdapter()->getKey())
				->findMirror('xml')
				->setPage("series/$id/")
				->request()
				->getResponse());

		return $this;

	}


	protected function _loadXml($xml)
	{

		$data = simplexml_load_string($xml);

		if (!$data instanceof \SimpleXMLElement) exit;

		foreach ($data->Series->children() as $child) {

			$this->_data[$child->getName()] = (string) $child;

		}

	}


	public function getId()
	{

		return (int) $this['id'];

	}


	public function getActors()
	{

		return $this->_explodeBarDelimitedString($this['Actors']);

	}


	public function getAirsDayOfWeek()
	{

		return $this['Airs_DayOfWeek'];

	}


	public function getAirsTime()
	{

		return $this['Airs_Time'];

	}


	public function getContentRating()
	{

		return $this['ContentRating'];

	}


	public function getFirstAired()
	{

		return $this['FirstAired'];

	}


	public function getGenres()
	{

		return $this->_explodeBarDelimitedString($this['Genre']);

	}

	public function getImdbId()
	{

		return $this['IMDB_ID'];

	}


	public function getLanguage()
	{

		return $this['Language'];

	}


	public function getNetwork()
	{

		return $this['Network'];

	}


	public function getNetworkId()
	{

		return $this['NetworkID'];

	}


	public function getOverview()
	{

		return $this['Overview'];

	}


	public function getRating()
	{

		return (float) $this['Rating'];

	}


	public function getRatingCount()
	{

		return (int) $this['RatingCount'];

	}


	public function getRuntime()
	{

		return (int) $this['Runtime'];

	}


	public function getSeriesId()
	{

		return (int) $this['SeriesID'];

	}


	public function getSeriesName()
	{

		return $this['SeriesName'];

	}


	public function getStatus()
	{

		return $this['Status'];

	}


	public function getAdded()
	{

		return $this['added'];

	}


	public function getAddedBy()
	{

		return $this['addedBy'];

	}


	public function getBanner()
	{

		$request = new namespace\Request();
		return $request->setKey($this->getAdapter()->getKey())
				->findMirror('banner')
				->getMirror() . "/banners/{$this['banner']}";

	}


	public function getFanart()
	{

		$request = new namespace\Request();
		return $request->setKey($this->getAdapter()->getKey())
				->findMirror('banner')
				->getMirror() . "/banners/{$this['fanart']}";

	}


	public function getLastUpdated()
	{

		return (int) $this['lastupdated'];

	}


	public function getPoster()
	{

		$request = new namespace\Request();
		return $request->setKey($this->getAdapter()->getKey())
				->findMirror('banner')
				->getMirror() . "/banners/{$this['poster']}";

	}


	public function getZap2itId()
	{

		return $this['zap2it_id'];

	}


}