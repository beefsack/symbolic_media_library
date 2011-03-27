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


	/**
	 * Gets a series
	 * @param integer $id
	 * @return Series
	 */
	public function getSeries($id)
	{

		$series = new Series($this);

		return $series->load($id);

	}


	/**
	 * Finds series by name
	 * @param string $name
	 * @return array
	 */
	public function findSeries($name)
	{

		$result = array();

		$request = new namespace\Request();
		$response = $request->setKey($this->getKey())
				->findMirror('xml')
				->setPage('/api/GetSeries.php')
				->setParam('seriesname', $name)
				->request()
				->getResponse();

		$data = simplexml_load_string($response);

		foreach ($data->Series as $s) {

			$series = new namespace\Series($this);
			$series->load($s->seriesid);
			$result[] = $series;

		}

		return $result;

	}


}


/**
 * The request class, which handles connections to the API
 */
class Request
{

	/**
	 * The base URL format
	 */
	const BASE_URL = '{{{mirror}}}{{{page}}}';


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


	/**
	 * Sets the page that will be requested
	 * @param string $page
	 * @return Request
	 */
	public function setPage($page)
	{

		$this->_page = $page;

		return $this;

	}


	/**
	 * Gets the page that will be requested
	 * @return string
	 */
	public function getPage()
	{

		return $this->_page;

	}


	/**
	 * Sets a request parameter
	 * @param string $name
	 * @param string $value
	 * @return Request
	 */
	public function setParam($name, $value)
	{

		$this->_params[$name] = $value;

		return $this;

	}


	/**
	 * Gets a request parameter
	 * @param string $name
	 * @return string
	 */
	public function getParam($name)
	{

		return $this->_params[$name];

	}


	/**
	 * Sets the request parameters
	 * @param array $params
	 * @return Request
	 */
	public function setParams(array $params)
	{

		$this->_params = $params;

		return $this;

	}


	/**
	 * Gets the request parameters
	 * @return array
	 */
	public function getParams()
	{

		return $this->_params;

	}


	/**
	 * Sets the mirror to request
	 * @param string $mirror
	 * @return Request
	 */
	public function setMirror($mirror)
	{

		$this->_mirror = $mirror;

		return $this;

	}


	/**
	 * Gets the mirror that will be requested
	 * @return string
	 */
	public function getMirror()
	{

		return $this->_mirror;

	}


	/**
	 * Finds a mirror of the required type
	 * @param string $type Possible values are xml|banner|zip
	 * @return string
	 */
	public function findMirror($type)
	{

		return $this->setMirror($this->getRandomMirror($type));

	}


	/**
	 * Gets an array of mirrors, keyed as types xml|banner|zip
	 * @return array
	 */
	public function getMirrors()
	{

		if (self::$_mirrors === null)
			self::$_mirrors = $this->parseMirrorListXml(
					$this->requestMirrorList());

		return self::$_mirrors;

	}


	/**
	 * Gets a random mirror of a type
	 * @param string $type Possible values are xml|banner|zip
	 * @return string
	 */
	public function getRandomMirror($type)
	{

		$mirrors = $this->getMirrors();

		return $mirrors[$type][array_rand($mirrors[$type])];

	}


	/**
	 * Requests the mirror list
	 * @return string
	 */
	protected function requestMirrorList()
	{

		$key = $this->getKey();

		$request = new self();
		$response = $request->setPage("/api/$key/mirrors.xml")
				->setKey($this->getKey())
				->request()
				->getResponse();

		return $response;

	}


	/**
	 * Parses mirror list XML into an object
	 * @param string $xml
	 * @return SimpleXMLElement
	 */
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

			$params[] = urlencode($key) . '=' . urlencode($value);

		}

		return implode('&', $params);

	}


	/**
	 * Performs the request
	 * @return Request
	 */
	public function request()
	{

		$ch = curl_init($this->getUrl());
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$this->_response = curl_exec($ch);
		curl_close($ch);

		return $this;
		
	}


	/**
	 * Gets the response of the last request
	 * @return string
	 */
	public function getResponse()
	{

		return $this->_response;

	}


	/**
	 * Gets the url for the request
	 * @return string
	 */
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


/**
 * The data class is the base class for the models.
 * It allows array access directly to the data, and enforces passing an adapter
 * on creation.
 */
class Data implements \ArrayAccess
{

	/**
	 * The actual data that the data model is modeling
	 * @var array
	 */
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


	/**
	 * Returns the data as a raw array.
	 * @return array
	 */
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


	/**
	 * Explodes a bar delimited string to an array
	 * @param string $string
	 * @return array
	 */
	protected function _explodeBarDelimitedString($string)
	{

		$str = trim($string, '|');

		if (!$str) return array();

		return explode('|', $str);

	}


}


/**
 * Model for a series in the database
 */
class Series extends Data
{

	/**
	 * Loads a series of the specified id
	 * @param integer $id
	 * @return Series
	 */
	public function load($id)
	{

		$key = $this->getAdapter()->getKey();

		$request = new Request();
		$this->loadXml($request->setKey($this->getAdapter()->getKey())
				->findMirror('xml')
				->setPage("/api/$key/series/$id/")
				->request()
				->getResponse());

		return $this;

	}


	/**
	 * Loads data from an xml string
	 * @param string $xml
	 */
	public function loadXml($xml)
	{

		$data = simplexml_load_string($xml);

		if (!$data instanceof \SimpleXMLElement) exit;

		foreach ($data->Series->children() as $child) {

			$this->_data[$child->getName()] = (string) $child;

		}

	}


	/**
	 * Gets the id for the series
	 * @return integer
	 */
	public function getId()
	{

		return (int) $this['id'];

	}


	/**
	 * Gets the actors for the series
	 * @return array
	 */
	public function getActors()
	{

		return $this->_explodeBarDelimitedString($this['Actors']);

	}


	/**
	 * Gets the day of the week the series airs
	 * @return string
	 */
	public function getAirsDayOfWeek()
	{

		return $this['Airs_DayOfWeek'];

	}


	/**
	 * Gets the time the series airs
	 * @return string
	 */
	public function getAirsTime()
	{

		return $this['Airs_Time'];

	}


	/**
	 * Gets the content rating for the series
	 * @return string
	 */
	public function getContentRating()
	{

		return $this['ContentRating'];

	}


	/**
	 * Gets the date the series first aired
	 * @return string
	 */
	public function getFirstAired()
	{

		return $this['FirstAired'];

	}


	/**
	 * Gets the genres of the series
	 * @return array
	 */
	public function getGenres()
	{

		return $this->_explodeBarDelimitedString($this['Genre']);

	}


	/**
	 * Gets the IMDB id of the series
	 * @return string
	 */
	public function getImdbId()
	{

		return $this['IMDB_ID'];

	}


	/**
	 * Gets the language of the series
	 * @return string
	 */
	public function getLanguage()
	{

		return $this['Language'];

	}


	/**
	 * Gets the network who run the series
	 * @return string
	 */
	public function getNetwork()
	{

		return $this['Network'];

	}


	/**
	 * Gets the network id of the network who runs the series
	 * @return string
	 */
	public function getNetworkId()
	{

		return $this['NetworkID'];

	}


	/**
	 * Gets an overview of the series
	 * @return string
	 */
	public function getOverview()
	{

		return $this['Overview'];

	}


	/**
	 * Gets the rating for the series
	 * @return float
	 */
	public function getRating()
	{

		return (float) $this['Rating'];

	}


	/**
	 * Gets the number of ratings for the series
	 * @return integer
	 */
	public function getRatingCount()
	{

		return (int) $this['RatingCount'];

	}


	/**
	 * Gets the runtime of the series
	 * @return integer
	 */
	public function getRuntime()
	{

		return (int) $this['Runtime'];

	}


	/**
	 * Gets the series id
	 * @return integer
	 */
	public function getSeriesId()
	{

		return (int) $this['SeriesID'];

	}


	/**
	 * Gets the name of the series
	 * @return string
	 */
	public function getSeriesName()
	{

		return $this['SeriesName'];

	}


	/**
	 * Gets the status of the series
	 * @return string
	 */
	public function getStatus()
	{

		return $this['Status'];

	}


	/**
	 * Gets when the series was added
	 * @return string
	 */
	public function getAdded()
	{

		return $this['added'];

	}


	/**
	 * Gets the person who added the series
	 * @return string
	 */
	public function getAddedBy()
	{

		return $this['addedBy'];

	}


	/**
	 * Gets the url for a banner for the series
	 * @return string
	 */
	public function getBanner()
	{

		$request = new namespace\Request();
		return $request->setKey($this->getAdapter()->getKey())
				->findMirror('banner')
				->setPage("/banners/{$this['banner']}")
				->getUrl();

	}


	/**
	 * Gets the url for some fanart for the series
	 * @return string
	 */
	public function getFanart()
	{

		$request = new namespace\Request();
		return $request->setKey($this->getAdapter()->getKey())
				->findMirror('banner')
				->setPage("/banners/{$this['fanart']}")
				->getUrl();

	}


	/**
	 * Gets when the series was last updated
	 * @return integer
	 */
	public function getLastUpdated()
	{

		return (int) $this['lastupdated'];

	}


	/**
	 * Gets the url for a poster for the series
	 * @return string
	 */
	public function getPoster()
	{

		$request = new namespace\Request();
		return $request->setKey($this->getAdapter()->getKey())
				->findMirror('banner')
				->setPage("/banners/{$this['poster']}")
				->getUrl();

	}


	/**
	 * Gets the Zap2it id for the series
	 * @return string
	 */
	public function getZap2itId()
	{

		return $this['zap2it_id'];

	}


}