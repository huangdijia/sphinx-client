<?php

declare(strict_types=1);
/**
 * This file is part of vendor/package.
 *
 * @link     https://github.com/vendor/package
 * @document https://github.com/vendor/package/blob/main/README.md
 * @contact  Your name <your-mail@gmail.com>
 */

namespace Huangdijia\SphinxClient;

use ErrorException;
use InvalidArgumentException;

class Client
{
    /**
     * Known searchd commands.
     */
    public const SEARCHD_COMMAND_SEARCH = 0;

    public const SEARCHD_COMMAND_EXCERPT = 1;

    public const SEARCHD_COMMAND_UPDATE = 2;

    public const SEARCHD_COMMAND_KEYWORDS = 3;

    public const SEARCHD_COMMAND_PERSIST = 4;

    public const SEARCHD_COMMAND_STATUS = 5;

    public const SEARCHD_COMMAND_FLUSHATTRS = 7;

    /**
     * Current client-side command implementation versions.
     */
    public const VER_COMMAND_SEARCH = 0x119;

    public const VER_COMMAND_EXCERPT = 0x104;

    public const VER_COMMAND_UPDATE = 0x102;

    public const VER_COMMAND_KEYWORDS = 0x100;

    public const VER_COMMAND_STATUS = 0x100;

    public const VER_COMMAND_QUERY = 0x100;

    public const VER_COMMAND_FLUSHATTRS = 0x100;

    /**
     * Known searchd status codes.
     */
    public const SEARCHD_OK = 0;

    public const SEARCHD_ERROR = 1;

    public const SEARCHD_RETRY = 2;

    public const SEARCHD_WARNING = 3;

    /**
     * Known match modes.
     */
    public const SPH_MATCH_ALL = 0;

    public const SPH_MATCH_ANY = 1;

    public const SPH_MATCH_PHRASE = 2;

    public const SPH_MATCH_BOOLEAN = 3;

    public const SPH_MATCH_EXTENDED = 4;

    public const SPH_MATCH_FULLSCAN = 5;

    /**
     * extended engine V2 (TEMPORARY, WILL BE REMOVED).
     */
    public const SPH_MATCH_EXTENDED2 = 6;

    /**
     * Known ranking modes (ext2 only), default mode, phrase proximity major factor and BM25 minor one.
     */
    public const SPH_RANK_PROXIMITY_BM25 = 0;

    /**
     * statistical mode, BM25 ranking only (faster but worse quality).
     */
    public const SPH_RANK_BM25 = 1;

    /**
     * no ranking, all matches get a weight of 1.
     */
    public const SPH_RANK_NONE = 2;

    /**
     * simple word-count weighting, rank is a weighted sum of per-field keyword occurence counts.
     */
    public const SPH_RANK_WORDCOUNT = 3;

    public const SPH_RANK_PROXIMITY = 4;

    public const SPH_RANK_MATCHANY = 5;

    public const SPH_RANK_FIELDMASK = 6;

    public const SPH_RANK_SPH04 = 7;

    public const SPH_RANK_EXPR = 8;

    public const SPH_RANK_TOTAL = 9;

    /**
     * Known sort modes.
     */
    public const SPH_SORT_RELEVANCE = 0;

    public const SPH_SORT_ATTR_DESC = 1;

    public const SPH_SORT_ATTR_ASC = 2;

    public const SPH_SORT_TIME_SEGMENTS = 3;

    public const SPH_SORT_EXTENDED = 4;

    public const SPH_SORT_EXPR = 5;

    /**
     * Known filter types.
     */
    public const SPH_FILTER_VALUES = 0;

    public const SPH_FILTER_RANGE = 1;

    public const SPH_FILTER_FLOATRANGE = 2;

    /**
     * Known attribute types.
     */
    public const SPH_ATTR_INTEGER = 1;

    public const SPH_ATTR_TIMESTAMP = 2;

    public const SPH_ATTR_ORDINAL = 3;

    public const SPH_ATTR_BOOL = 4;

    public const SPH_ATTR_FLOAT = 5;

    public const SPH_ATTR_BIGINT = 6;

    public const SPH_ATTR_STRING = 7;

    public const SPH_ATTR_MULTI = 0x40000001;

    public const SPH_ATTR_MULTI64 = 0x40000002;

    /**
     * Known grouping functions.
     */
    public const SPH_GROUPBY_DAY = 0;

    public const SPH_GROUPBY_WEEK = 1;

    public const SPH_GROUPBY_MONTH = 2;

    public const SPH_GROUPBY_YEAR = 3;

    public const SPH_GROUPBY_ATTR = 4;

    public const SPH_GROUPBY_ATTRPAIR = 5;

    // searchd host (default is "localhost")
    public string $host = 'localhost';

    // searchd port (default is 9312)
    public int $port = 9312;

    // socket path
    public string|bool $path = false;

    /**
     * socket connection.
     * @var false|resource
     */
    public $socket = false;

    /**
     * how many records to seek from result-set start (default is 0).
     */
    public int $offset = 0;

    /**
     * how many records to return from result-set starting at offset (default is 20).
     */
    public int $limit = 20;

    /**
     * query matching mode (default is SPH_MATCH_ALL).
     */
    public int $mode = self::SPH_MATCH_ALL;

    /**
     * per-field weights (default is 1 for all fields).
     * @var int[]
     */
    public array $weights = [];

    /**
     * match sorting mode (default is SPH_SORT_RELEVANCE).
     */
    public int $sort = self::SPH_SORT_RELEVANCE;

    /**
     * attribute to sort by (default is "").
     */
    public string $sortby = '';

    /**
     * min ID to match (default is 0, which means no limit).
     */
    public int $minId = 0;

    /**
     * max ID to match (default is 0, which means no limit).
     */
    public int $maxId = 0;

    /**
     * search filters.
     */
    public array $filters = [];

    /**
     * group-by attribute name.
     */
    public string $groupBy = '';

    /**
     * group-by function (to pre-process group-by attribute value with).
     */
    public int $groupFunc = self::SPH_GROUPBY_DAY;

    /**
     * group-by sorting clause (to sort groups in result set with).
     */
    public string $groupSort = '@group desc';

    /**
     * group-by count-distinct attribute.
     */
    public string $groupDistinct = '';

    /**
     * max matches to retrieve.
     */
    public int $maxMatches = 1000;

    /**
     * cutoff to stop searching at (default is 0).
     */
    public int $cutoff = 0;

    /**
     * distributed retries count.
     */
    public int $retryCount = 0;

    /**
     * distributed retries delay.
     */
    public int $retryDelay = 0;

    /**
     * geographical anchor point.
     */
    public array $anchor = [];

    /**
     * per-index weights.
     *
     * @var array<string, int>
     */
    public array $indexWeights = [];

    /**
     * ranking mode (default is SPH_RANK_PROXIMITY_BM25).
     */
    public int $ranker = self::SPH_RANK_PROXIMITY_BM25;

    /**
     * ranking mode expression (for SPH_RANK_EXPR).
     */
    public string $rankExpr = '';

    /**
     * max query time, milliseconds (default is 0, do not limit).
     */
    public int $maxQueryTime = 0;

    /**
     * per-field-name weights.
     * @var array<string, int>
     */
    public array $fieldWeights = [];

    /**
     * per-query attribute values overrides.
     */
    public array $overrides = [];

    /**
     * select-list (attributes or expressions, with optional aliases).
     */
    public string $select = '*';

    /**
     * last error message.
     */
    public string $error = '';

    /**
     * last warning message.
     */
    public string $warning;

    /**
     * connection error vs remote error flag.
     */
    public bool $connError = false;

    /**
     * requests array for multi-query.
     */
    public array $reqs = [];

    /**
     * stored mbstring encoding.
     */
    public string $mbenc = '';

    /**
     * whether $result["matches"] should be a hash or an array.
     */
    public bool $arrayResult = false;

    /**
     * connect timeout.
     */
    public int $timeout = 0;

    /**
     * Create a new client object and fill defaults.
     */
    public function __construct(array $config = [])
    {
        $this->host = $config['host'] ?? 'localhost';
        $this->port = $config['port'] ?? 9312;
        $this->limit = (int) ($config['limit'] ?? 20);
        $this->mode = (int) ($config['mode'] ?? self::SPH_MATCH_ALL);
        $this->maxMatches = (int) ($config['max_matches'] ?? 1000);
        $this->retryCount = (int) ($config['retry_count'] ?? 0);
        $this->retryDelay = (int) ($config['retry_delay'] ?? 0);
        $this->ranker = (int) ($config['ranker'] ?? self::SPH_RANK_PROXIMITY_BM25);
        $this->maxQueryTime = (int) ($config['max_query_time'] ?? 0);
        $this->arrayResult = (bool) ($config['array_result'] ?? false);
        $this->timeout = (int) ($config['timeout'] ?? 0);
    }

    /**
     * Close the socket upon exit.
     */
    public function __destruct()
    {
        if ($this->socket && $this->socket !== false) {
            fclose($this->socket);
        }
    }

    /**
     * Get last error message.
     */
    public function getLastError(): string
    {
        return $this->error;
    }

    /**
     * Get last warning message.
     */
    public function getLastWarning(): string
    {
        return $this->warning;
    }

    /**
     * Get last error flag, to tell network connection errors from searchd errors or broken responses.
     */
    public function isConnectError(): bool
    {
        return $this->connError;
    }

    /**
     * Set searchd host name and port.
     *
     * @return $this
     * @throws InvalidArgumentException When host name or port number is invalid
     */
    public function setServer(string $host, int $port = 0)
    {
        if ($host[0] === '/') {
            $this->path = 'unix://' . $host;

            return $this;
        }

        if (substr($host, 0, 7) === 'unix://') {
            $this->path = $host;

            return $this;
        }

        $this->host = $host;

        $port = intval($port);
        if ($port < 0 || $port >= 65536) {
            throw new InvalidArgumentException('Port number must be an integer between 0 and 65536.');
        }

        $this->port = $port ?: 9312;
        $this->path = '';

        return $this;
    }

    /**
     * Set server connection timeout.
     *
     * @return $this
     * @throws InvalidArgumentException When $timeout is negative
     */
    public function setConnectTimeout(int $timeout)
    {
        if ($timeout < 0) {
            throw new InvalidArgumentException('Timeout cannot be negative.');
        }

        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Set offset and count into result set, optionally set max-matches and cutoff limits.
     *
     * @return $this
     * @throws InvalidArgumentException When offset, limit, max or cutoff is outside valid ranges
     */
    public function setLimits(int $offset, int $limit, int $max = 0, int $cutoff = 0)
    {
        if ($offset < 0) {
            throw new InvalidArgumentException('Offset cannot be negative.');
        }

        if ($limit <= 0) {
            throw new InvalidArgumentException('Limit must be positive.');
        }

        if ($max < 0) {
            throw new InvalidArgumentException('Maximum matches cannot be negative.');
        }

        if ($cutoff < 0) {
            throw new InvalidArgumentException('Cutoff cannot be negative.');
        }

        $this->offset = $offset;
        $this->limit = $limit;

        if ($max) {
            $this->maxMatches = $max;
        }

        if ($cutoff) {
            $this->cutoff = $cutoff;
        }

        return $this;
    }

    /**
     * Set maximum query time, in milliseconds, per-index. 0 means "do not limit".
     *
     * @return $this
     * @throws InvalidArgumentException When $max is negative
     */
    public function setMaxQueryTime(int $max)
    {
        if ($max < 0) {
            throw new InvalidArgumentException('Maximum query time cannot be negative.');
        }

        $this->maxQueryTime = $max;

        return $this;
    }

    /**
     * Set matching mode.
     *
     * @return $this
     * @throws InvalidArgumentException When match mode is invalid
     */
    public function setMatchMode(int $mode)
    {
        if (! in_array(
            $mode,
            [
                self::SPH_MATCH_ALL,
                self::SPH_MATCH_ANY,
                self::SPH_MATCH_PHRASE,
                self::SPH_MATCH_BOOLEAN,
                self::SPH_MATCH_EXTENDED,
                self::SPH_MATCH_FULLSCAN,
                self::SPH_MATCH_EXTENDED2,
            ]
        )) {
            throw new InvalidArgumentException('Matching mode is invalid.');
        }

        $this->mode = $mode;

        return $this;
    }

    /**
     * Set ranking mode.
     *
     * @return $this
     * @throws InvalidArgumentException When ranking mode or expression is invalid
     */
    public function setRankingMode(int $ranker, string $rankExpr = '')
    {
        if (! in_array(
            $ranker,
            [
                self::SPH_RANK_PROXIMITY_BM25,
                self::SPH_RANK_BM25,
                self::SPH_RANK_NONE,
                self::SPH_RANK_WORDCOUNT,
                self::SPH_RANK_PROXIMITY,
                self::SPH_RANK_MATCHANY,
                self::SPH_RANK_FIELDMASK,
                self::SPH_RANK_SPH04,
                self::SPH_RANK_EXPR,
                self::SPH_RANK_TOTAL,
            ]
        )) {
            throw new InvalidArgumentException('Ranking mode is invalid.');
        }

        if ($ranker === self::SPH_RANK_EXPR && ! $rankExpr) {
            throw new InvalidArgumentException('Current ranking mode must have a ranking expression.');
        }

        $this->ranker = $ranker;
        $this->rankExpr = $rankExpr;

        return $this;
    }

    /**
     * Set matches sorting mode.
     *
     * @return $this
     * @throws InvalidArgumentException When sorting mode or expression is invalid
     */
    public function setSortMode(int $mode, string $sortBy = '')
    {
        if (! in_array(
            $mode,
            [
                self::SPH_SORT_RELEVANCE,
                self::SPH_SORT_ATTR_DESC,
                self::SPH_SORT_ATTR_ASC,
                self::SPH_SORT_TIME_SEGMENTS,
                self::SPH_SORT_EXTENDED,
                self::SPH_SORT_EXPR,
            ]
        )) {
            throw new InvalidArgumentException('Sorting mode is invalid.');
        }

        if ($mode !== self::SPH_SORT_RELEVANCE && ! $sortBy) {
            throw new InvalidArgumentException('Current sorting mode must have a sorting expression.');
        }

        $this->sort = $mode;
        $this->sortby = $sortBy;

        return $this;
    }

    /**
     * Bind per-field weights by order.
     *
     * @deprecated use SetFieldWeights() instead
     *
     * @param int[] $weights
     *
     * @return $this
     * @throws InvalidArgumentException When weight is invalid
     */
    public function setWeights(array $weights)
    {
        foreach ($weights as $weight) {
            if (! is_int($weight)) {
                throw new InvalidArgumentException('Weight must be an integer.');
            }
        }

        $this->weights = $weights;

        return $this;
    }

    /**
     * Bind per-field weights by name.
     *
     * @param array<string,int> $weights
     *
     * @return $this
     * @throws InvalidArgumentException When field name or weight is invalid
     */
    public function setFieldWeights(array $weights)
    {
        foreach ($weights as $name => $weight) {
            if (! is_string($name)) {
                throw new InvalidArgumentException('Field name must be a string.');
            }

            if (! is_int($weight)) {
                throw new InvalidArgumentException('Field weight must be an integer.');
            }
        }

        $this->fieldWeights = $weights;

        return $this;
    }

    /**
     * Bind per-index weights by name.
     *
     * @param array<string,int> $weights
     *
     * @return $this
     * @throws InvalidArgumentException When index name or weight is invalid
     */
    public function setIndexWeights(array $weights)
    {
        foreach ($weights as $index => $weight) {
            if (! is_string($index)) {
                throw new InvalidArgumentException('Index name must be a string.');
            }

            if (! is_int($weight)) {
                throw new InvalidArgumentException('Index weight must be an integer.');
            }
        }

        $this->indexWeights = $weights;

        return $this;
    }

    /**
     * Limit the ID range; only match records if document ID is between $min and $max (inclusive).
     *
     * @param int $min minimum document ID
     * @param int $max maximum document ID
     *
     * @return $this
     * @throws InvalidArgumentException When $min or $max are invalid
     */
    public function setIdRange(int $min, int $max)
    {
        if ($min > $max) {
            throw new InvalidArgumentException('Minimum ID cannot be larger than maximum ID.');
        }

        $this->minId = $min;
        $this->maxId = $max;

        return $this;
    }

    /**
     * Set values filter; only match records where $attribute value is in (or not in) the given set.
     *
     * @param string $attribute attribute name
     * @param array $values value set
     * @param bool $exclude whether the filter is exclusive or inclusive
     *
     * @return $this
     * @throws InvalidArgumentException When attribute name or value array is invalid
     */
    public function setFilter(string $attribute, array $values, bool $exclude = false)
    {
        if (! count($values)) {
            throw new InvalidArgumentException('Values array must not be empty.');
        }

        foreach ($values as $value) {
            if (! is_numeric($value)) {
                throw new InvalidArgumentException('Value must be numeric.');
            }
        }

        $exclude = (bool) $exclude;
        $this->filters[] = [
            'type' => self::SPH_FILTER_VALUES,
            'attr' => $attribute,
            'exclude' => $exclude,
            'values' => $values,
        ];

        return $this;
    }

    /**
     * Set range filter; only match records if $attribute value between $min and $max (inclusive).
     *
     * @param string $attribute attribute name
     * @param int $min minimum attribute value
     * @param int $max maximum attribute value
     * @param bool $exclude whether the filter is exclusive or inclusive
     *
     * @return $this
     * @throws InvalidArgumentException When $attribute, $min, $max or $exclude is invalid
     */
    public function setFilterRange(string $attribute, int $min, int $max, bool $exclude = false)
    {
        if ($min > $max) {
            throw new InvalidArgumentException('Minimum value cannot be larger than maximum value.');
        }

        $this->filters[] = [
            'type' => self::SPH_FILTER_RANGE,
            'attr' => $attribute,
            'exclude' => $exclude,
            'min' => $min,
            'max' => $max,
        ];

        return $this;
    }

    /**
     * Set float range filter; only match records if $attribute value between $min and $max (inclusive).
     *
     * @param string $attribute attribute name
     * @param float $min minimum attribute value
     * @param float $max maximum attribute value
     * @param bool $exclude whether the filter is exclusive or inclusive
     *
     * @return $this
     * @throws InvalidArgumentException When $attribute, $min, $max or $exclude is invalid
     */
    public function setFilterFloatRange(string $attribute, float $min, float $max, bool $exclude = false)
    {
        if ($min > $max) {
            throw new InvalidArgumentException('Minimum value cannot be larger than maximum value.');
        }

        $this->filters[] = [
            'type' => self::SPH_FILTER_FLOATRANGE,
            'attr' => $attribute,
            'exclude' => $exclude,
            'min' => $min,
            'max' => $max,
        ];

        return $this;
    }

    /**
     * Set up anchor point for geosphere distance calculations. Required to use @geodist in filters and sorting.
     *
     * @param string $attrLat latitude attribute name
     * @param string $attrLong longitude attribute name
     * @param float|int $lat anchor point latitude (in radians)
     * @param float|int $long anchor point longitude (in radians)
     *
     * @return $this
     * @throws InvalidArgumentException When attribute names or coordinates are invalid
     */
    public function setGeoAnchor(string $attrLat, string $attrLong, float|int $lat, float|int $long)
    {
        $this->anchor = [
            'attrlat' => $attrLat,
            'attrlong' => $attrLong,
            'lat' => $lat,
            'long' => $long,
        ];

        return $this;
    }

    /**
     * Set grouping attribute and function.
     *
     * @param string $attribute attribute name
     * @param int $func grouping function
     * @param string $groupSort group sorting clause
     *
     * @return $this
     * @throws InvalidArgumentException When attribute name, group clause or function is invalid
     */
    public function setGroupBy(string $attribute, int $func, string $groupSort = '@group desc')
    {
        if (! in_array(
            $func,
            [
                self::SPH_GROUPBY_DAY,
                self::SPH_GROUPBY_WEEK,
                self::SPH_GROUPBY_MONTH,
                self::SPH_GROUPBY_YEAR,
                self::SPH_GROUPBY_ATTR,
                self::SPH_GROUPBY_ATTRPAIR,
            ]
        )) {
            throw new InvalidArgumentException('Grouping function is invalid.');
        }

        $this->groupBy = $attribute;
        $this->groupFunc = $func;
        $this->groupSort = $groupSort;

        return $this;
    }

    /**
     * Set count-distinct attribute for group-by queries.
     *
     * @param string $attribute attribute name
     *
     * @return $this
     * @throws InvalidArgumentException When attribute name is invalid
     */
    public function setGroupDistinct(string $attribute)
    {
        $this->groupDistinct = $attribute;

        return $this;
    }

    /**
     * Set distributed retries count and delay.
     *
     * @return $this
     * @throws InvalidArgumentException When retry count or delay is negative
     */
    public function setRetries(int $count, int $delay = 0)
    {
        if ($count < 0) {
            throw new InvalidArgumentException('Retry count cannot be negative.');
        }

        if ($delay < 0) {
            throw new InvalidArgumentException('Retry delay cannot be negative.');
        }

        $this->retryCount = $count;
        $this->retryDelay = $delay;

        return $this;
    }

    /**
     * Set resultset format to either hash or array; hash is the default format.
     *
     * PHP specific; needed for group-by-MVA result sets that may contain duplicate IDs
     *
     * @param bool $arrayResult whether to return results as array keyed by ID
     *
     * @return $this
     */
    public function setArrayResult(bool $arrayResult)
    {
        $this->arrayResult = $arrayResult;

        return $this;
    }

    /**
     * Set attribute values override. Only one override per attribute.
     *
     * @param string $attr attribute name
     * @param int $type attribute type
     * @param array $values hash that maps document IDs to attribute values
     *
     * @return $this
     * @throws InvalidArgumentException When attribute name or type is invalid
     */
    public function setOverride(string $attr, int $type, array $values)
    {
        if (! in_array(
            $type,
            [
                self::SPH_ATTR_INTEGER,
                self::SPH_ATTR_TIMESTAMP,
                self::SPH_ATTR_BOOL,
                self::SPH_ATTR_FLOAT,
                self::SPH_ATTR_BIGINT,
            ]
        )) {
            throw new InvalidArgumentException('Attribute type is invalid.');
        }

        $this->overrides[$attr] = [
            'attr' => $attr,
            'type' => $type,
            'values' => $values,
        ];

        return $this;
    }

    /**
     * Set select-list (attributes or expressions), SQL-like syntax.
     *
     * @param string $select select list
     *
     * @return $this
     * @throws InvalidArgumentException When select list is invalid
     */
    public function setSelect(string $select)
    {
        $this->select = $select;

        return $this;
    }

    /**
     * Clear all filters (for multi-queries).
     *
     * @return $this
     */
    public function resetFilters()
    {
        $this->filters = [];
        $this->anchor = [];

        return $this;
    }

    /**
     * Clear groupby settings (for multi-queries).
     *
     * @return $this
     */
    public function resetGroupBy()
    {
        $this->groupBy = '';
        $this->groupFunc = self::SPH_GROUPBY_DAY;
        $this->groupSort = '@group desc';
        $this->groupDistinct = '';

        return $this;
    }

    /**
     * Clear all attribute value overrides (for multi-queries).
     *
     * @return $this
     */
    public function resetOverrides()
    {
        $this->overrides = [];

        return $this;
    }

    /**
     * Connect to searchd server, run given search query through given indexes, and return the results.
     *
     * @param string $query query string
     * @param string $index index name
     * @param string $comment optional comment
     *
     * @return array|false results array, or false upon error
     * @throws ErrorException When request array is not empty initially
     */
    public function query(string $query, string $index = '*', string $comment = '')
    {
        if (! empty($this->reqs)) {
            throw new ErrorException('Request array must be empty.');
        }

        $this->addQuery($query, $index, $comment);
        $results = $this->runQueries();

        // just in case it failed too early
        $this->reqs = [];

        if (! is_array($results)) {
            // probably network error; error message should be already filled
            return false;
        }

        $this->error = $results[0]['error'];
        $this->warning = $results[0]['warning'];

        if ($results[0]['status'] === self::SEARCHD_ERROR) {
            return false;
        }

        return $results[0];
    }

    /**
     * Add a query to a multi-query batch. Returns index into results array from runQueries() call.
     *
     * @return int results array index
     * @throws InvalidArgumentException When filter type, document ID or attribute value is invalid
     */
    public function addQuery(string $query, string $index = '*', string $comment = '')
    {
        // mbstring workaround
        $this->mbPush();

        // build request
        $req = pack('NNNN', $this->offset, $this->limit, $this->mode, $this->ranker);
        if ($this->ranker === self::SPH_RANK_EXPR) {
            $req .= pack('N', strlen($this->rankExpr)) . $this->rankExpr;
        }

        // (deprecated) sort mode
        $req .= pack('N', $this->sort);
        $req .= pack('N', strlen($this->sortby)) . $this->sortby;
        $req .= pack('N', strlen($query)) . $query;
        $req .= pack('N', count($this->weights));

        foreach ($this->weights as $weight) {
            $req .= pack('N', (int) $weight);
        }

        $req .= pack('N', strlen($index)) . $index;
        // id64 range marker
        $req .= pack('N', 1);
        // id64 range
        $req .= $this->packU64($this->minId) . $this->packU64($this->maxId);

        // filters
        $req .= pack('N', count($this->filters));
        foreach ($this->filters as $filter) {
            $req .= pack('N', strlen($filter['attr'])) . $filter['attr'];
            $req .= pack('N', $filter['type']);
            switch ($filter['type']) {
                case self::SPH_FILTER_VALUES:
                    $req .= pack('N', count($filter['values']));
                    foreach ($filter['values'] as $value) {
                        $req .= $this->packI64($value);
                    }
                    break;
                case self::SPH_FILTER_RANGE:
                    $req .= $this->packI64($filter['min']) . $this->packI64($filter['max']);
                    break;
                case self::SPH_FILTER_FLOATRANGE:
                    $req .= $this->packFloat($filter['min']) . $this->packFloat($filter['max']);
                    break;
                default:
                    throw new InvalidArgumentException('internal error: unhandled filter type');
            }

            $req .= pack('N', $filter['exclude']);
        }

        // group-by clause, max-matches count, group-sort clause, cutoff count
        $req .= pack('NN', $this->groupFunc, strlen($this->groupBy)) . $this->groupBy;
        $req .= pack('N', $this->maxMatches);
        $req .= pack('N', strlen($this->groupSort)) . $this->groupSort;
        $req .= pack('NNN', $this->cutoff, $this->retryCount, $this->retryDelay);
        $req .= pack('N', strlen($this->groupDistinct)) . $this->groupDistinct;

        // anchor point
        if (empty($this->anchor)) {
            $req .= pack('N', 0);
        } else {
            $a = &$this->anchor;
            $req .= pack('N', 1);
            $req .= pack('N', strlen($a['attrlat'])) . $a['attrlat'];
            $req .= pack('N', strlen($a['attrlong'])) . $a['attrlong'];
            $req .= $this->packFloat($a['lat']) . $this->packFloat($a['long']);
        }

        // per-index weights
        $req .= pack('N', count($this->indexWeights));
        foreach ($this->indexWeights as $idx => $weight) {
            $req .= pack('N', strlen((string) $idx)) . $idx . pack('N', $weight);
        }

        // max query time
        $req .= pack('N', $this->maxQueryTime);

        // per-field weights
        $req .= pack('N', count($this->fieldWeights));
        foreach ($this->fieldWeights as $field => $weight) {
            $req .= pack('N', strlen((string) $field)) . $field . pack('N', $weight);
        }

        // comment
        $req .= pack('N', strlen($comment)) . $comment;

        // attribute overrides
        $req .= pack('N', count($this->overrides));
        foreach ($this->overrides as $key => $entry) {
            $req .= pack('N', strlen($entry['attr'])) . $entry['attr'];
            $req .= pack('NN', $entry['type'], count($entry['values']));
            foreach ($entry['values'] as $id => $val) {
                if (! is_numeric($id)) {
                    throw new InvalidArgumentException('Document ID must be numeric.');
                }

                if (! is_numeric($val)) {
                    throw new InvalidArgumentException('Attribute value must be numeric.');
                }

                $req .= $this->packU64($id);
                switch ($entry['type']) {
                    case self::SPH_ATTR_FLOAT:
                        $req .= $this->packFloat($val);
                        break;
                    case self::SPH_ATTR_BIGINT:
                        $req .= $this->packI64($val);
                        break;
                    default:
                        $req .= pack('N', $val);
                        break;
                }
            }
        }

        // select-list
        $req .= pack('N', strlen($this->select)) . $this->select;

        // mbstring workaround
        $this->mbPop();

        // store request to requests array
        $this->reqs[] = $req;

        return count($this->reqs) - 1;
    }

    /**
     * Connect to searchd, run batch queries, and return an array of results.
     *
     * @return array|false result array
     */
    public function runQueries()
    {
        if (empty($this->reqs)) {
            $this->error = 'No queries defined, issue addQuery() first';

            return false;
        }

        // mbstring workaround
        $this->mbPush();

        if (! ($fp = $this->connect())) {
            $this->mbPop();

            return false;
        }

        // send query, get response
        $nreqs = count($this->reqs);
        $req = implode('', $this->reqs);
        $len = 8 + strlen($req);

        // add header
        $req = pack('nnNNN', self::SEARCHD_COMMAND_SEARCH, self::VER_COMMAND_SEARCH, $len, 0, $nreqs) . $req;

        if (! $this->send($fp, $req, $len + 8) || ! ($response = $this->getResponse($fp, self::VER_COMMAND_SEARCH))) {
            $this->mbPop();

            return false;
        }

        // query sent ok; we can reset reqs now
        $this->reqs = [];

        // parse and return response
        return $this->parseSearchResponse($response, $nreqs);
    }

    /**
     * Connect to searchd and generate excerpts (snippets) from given documents for a given query.
     *
     * @param string[] $docs array of strings that carry the document contents
     * @param string $index name of the index
     * @param string $words string that contains the keywords to highlight
     * @param array $opts hash which contains additional optional highlighting parameters
     *
     * @return array|false array of snippets, or false on failure
     * @throws InvalidArgumentException When documents, index name or keywords are invalid
     */
    public function buildExcerpts(array $docs, string $index, string $words, array $opts = [])
    {
        foreach ($docs as $doc) {
            if (! is_string($doc)) {
                throw new InvalidArgumentException('Document must be a string.');
            }
        }

        $this->mbPush();

        if (! ($fp = $this->connect())) {
            $this->mbPop();

            return false;
        }

        // default options
        $defaults = [
            'before_match' => '<b>',
            'after_match' => '</b>',
            'chunk_separator' => ' ... ',
            'limit' => 256,
            'limit_passages' => 0,
            'limit_words' => 0,
            'around' => 5,
            'exact_phrase' => false,
            'single_passage' => false,
            'use_boundaries' => false,
            'weight_order' => false,
            'query_mode' => false,
            'force_all_words' => false,
            'start_passage_id' => 1,
            'load_files' => false,
            'html_strip_mode' => 'index',
            'allow_empty' => false,
            'passage_boundary' => 'none',
            'emit_zones' => false,
            'load_files_scattered' => false,
        ];

        foreach ($defaults as $opt => $default) {
            if (! isset($opts[$opt])) {
                $opts[$opt] = $default;
            }
        }

        // build request
        // v.1.2 req
        $flags = 1;

        foreach ([
            2 => 'exact_phrase',
            4 => 'single_passage',
            8 => 'use_boundaries',
            16 => 'weight_order',
            32 => 'query_mode',
            64 => 'force_all_words',
            128 => 'load_files',
            256 => 'allow_empty',
            512 => 'emit_zones',
            1024 => 'load_files_scattered',
        ] as $flag => $opt) {
            if ((bool) $opts[$opt]) {
                $flags |= $flag;
            }
        }

        // mode=0, flags=$flags
        $req = pack('NN', 0, $flags);
        $req .= pack('N', strlen($index)) . $index;
        $req .= pack('N', strlen($words)) . $words;

        // options
        $req .= pack('N', strlen($opts['before_match'])) . $opts['before_match'];
        $req .= pack('N', strlen($opts['after_match'])) . $opts['after_match'];
        $req .= pack('N', strlen($opts['chunk_separator'])) . $opts['chunk_separator'];
        $req .= pack('NN', (int) $opts['limit'], (int) $opts['around']);
        // v.1.2
        $req .= pack('NNN', (int) $opts['limit_passages'], (int) $opts['limit_words'], (int) $opts['start_passage_id']);
        $req .= pack('N', strlen($opts['html_strip_mode'])) . $opts['html_strip_mode'];
        $req .= pack('N', strlen($opts['passage_boundary'])) . $opts['passage_boundary'];

        // documents
        $req .= pack('N', count($docs));
        foreach ($docs as $doc) {
            $req .= pack('N', strlen($doc)) . $doc;
        }

        // send query, get response
        $len = strlen($req);
        // add header
        $req = pack('nnN', self::SEARCHD_COMMAND_EXCERPT, self::VER_COMMAND_EXCERPT, $len) . $req;

        if (! $this->send($fp, $req, $len + 8) || ! ($response = $this->getResponse($fp, self::VER_COMMAND_EXCERPT))) {
            $this->mbPop();

            return false;
        }

        // parse response
        $pos = 0;
        $res = [];
        $rlen = strlen($response);
        $ndoc = count($docs);

        for ($i = 0; $i < $ndoc; ++$i) {
            [, $len] = unpack('N*', substr($response, $pos, 4));

            $pos += 4;

            if ($pos + $len > $rlen) {
                $this->error = 'incomplete reply';
                $this->mbPop();

                return false;
            }

            $res[] = $len ? substr($response, $pos, $len) : '';
            $pos += $len;
        }

        $this->mbPop();

        return $res;
    }

    /**
     * Extracts keywords from query using tokenizer settings for a given index.
     *
     * @param string $query query to extract keywords from
     * @param string $index name of the index to get tokenizing settings and keyword occurrence statistics from
     * @param bool $hits whether keyword occurrence statistics are required
     *
     * @return array|false array of hashes with per-keyword information, or false on failure
     * @throws InvalidArgumentException When query or index name is not valid string
     */
    public function buildKeywords(string $query, string $index, bool $hits)
    {
        $this->mbPush();

        if (! ($fp = $this->connect())) {
            $this->mbPop();

            return false;
        }

        // build request
        // v.1.0 req

        $req = pack('N', strlen($query)) . $query;
        $req .= pack('N', strlen($index)) . $index;
        $req .= pack('N', (int) $hits);

        // send query, get response
        $len = strlen($req);
        // add header
        $req = pack('nnN', self::SEARCHD_COMMAND_KEYWORDS, self::VER_COMMAND_KEYWORDS, $len) . $req;
        if (! $this->send($fp, $req, $len + 8) || ! ($response = $this->getResponse($fp, self::VER_COMMAND_KEYWORDS))) {
            $this->mbPop();

            return false;
        }

        // parse response
        $pos = 0;
        $res = [];
        $rlen = strlen($response);
        [, $nwords] = unpack('N*', substr($response, $pos, 4));
        $pos += 4;

        for ($i = 0; $i < $nwords; ++$i) {
            [, $len] = unpack('N*', substr($response, $pos, 4));
            $pos += 4;

            $tokenized = $len ? substr($response, $pos, $len) : '';
            $pos += $len;

            [, $len] = unpack('N*', substr($response, $pos, 4));
            $pos += 4;

            $normalized = $len ? substr($response, $pos, $len) : '';
            $pos += $len;

            $res[] = ['tokenized' => $tokenized, 'normalized' => $normalized];

            if ($hits) {
                [$ndocs, $nhits] = array_values(unpack('N*N*', substr($response, $pos, 8)));
                $pos += 8;

                $res[$i]['docs'] = $ndocs;
                $res[$i]['hits'] = $nhits;
            }

            if ($pos > $rlen) {
                $this->error = 'incomplete reply';
                $this->mbPop();

                return false;
            }
        }

        $this->mbPop();

        return $res;
    }

    /**
     * Escapes characters that are treated as special operators by the query language parser.
     */
    public function escapeString(string $string): string
    {
        $from = ['\\', '(', ')', '|', '-', '!', '@', '~', '"', '&', '/', '^', '$', '='];
        $to = ['\\\\', '\(', '\)', '\|', '\-', '\!', '\@', '\~', '\"', '\&', '\/', '\^', '\$', '\='];

        return str_replace($from, $to, $string);
    }

    /**
     * Batch update given attributes in given documents.
     *
     * @param string $index search index
     * @param array<string> $attrs array of attribute names
     * @param array $values hash of arrays of new attribute values keyed by document ID
     * @param bool $mva whether to treat attributes as MVAs
     *
     * @return int Amount of updated documents (0 or more) on success, -1 on failure
     * @throws InvalidArgumentException When inputs do not match required types
     */
    public function updateAttributes(string $index, array $attrs, array $values, bool $mva = false)
    {
        foreach ($attrs as $attr) {
            if (! is_string($attr)) {
                throw new InvalidArgumentException('Attribute name must be a string.');
            }
        }

        foreach ($values as $id => $entry) {
            if (! is_numeric($id)) {
                throw new InvalidArgumentException('Document ID must be numeric.');
            }

            if (! is_array($entry)) {
                throw new InvalidArgumentException('Document must be an array of attribute values.');
            }

            if (count($entry) !== count($attrs)) {
                throw new InvalidArgumentException('Number of attributes do not match.');
            }

            foreach ($entry as $v) {
                if ($mva) {
                    if (! is_array($v)) {
                        throw new InvalidArgumentException('MVA must be an array.');
                    }

                    foreach ($v as $vv) {
                        if (! is_int($vv)) {
                            throw new InvalidArgumentException('Attribute value must be an integer.');
                        }
                    }
                } else {
                    if (! is_int($v)) {
                        throw new InvalidArgumentException('Attribute value must be an integer.');
                    }
                }
            }
        }

        // build request
        $this->mbPush();
        $req = pack('N', strlen($index)) . $index;

        $req .= pack('N', count($attrs));
        foreach ($attrs as $attr) {
            $req .= pack('N', strlen($attr)) . $attr;
            $req .= pack('N', $mva ? 1 : 0);
        }

        $req .= pack('N', count($values));
        foreach ($values as $id => $entry) {
            $req .= $this->packU64($id);
            foreach ($entry as $v) {
                $req .= pack('N', $mva ? count($v) : $v);
                if ($mva) {
                    foreach ($v as $vv) {
                        $req .= pack('N', $vv);
                    }
                }
            }
        }

        // connect, send query, get response
        if (! ($fp = $this->connect())) {
            $this->mbPop();

            return -1;
        }

        $len = strlen($req);
        $req = pack('nnN', self::SEARCHD_COMMAND_UPDATE, self::VER_COMMAND_UPDATE, $len) . $req; // add header
        if (! $this->send($fp, $req, $len + 8)) {
            $this->mbPop();

            return -1;
        }

        if (! ($response = $this->getResponse($fp, self::VER_COMMAND_UPDATE))) {
            $this->mbPop();

            return -1;
        }

        // parse response
        [, $updated] = unpack('N*', substr($response, 0, 4));
        $this->mbPop();

        return $updated;
    }

    /**
     * Open a persistent connection.
     */
    public function open(): bool
    {
        if ($this->socket !== false) {
            $this->error = 'already connected';

            return false;
        }

        if (! $fp = $this->connect()) {
            return false;
        }

        // command, command version = 0, body length = 4, body = 1
        $req = pack('nnNN', self::SEARCHD_COMMAND_PERSIST, 0, 4, 1);
        if (! $this->send($fp, $req, 12)) {
            return false;
        }

        $this->socket = $fp;

        return true;
    }

    /**
     * Close a persistent connection.
     */
    public function close(): bool
    {
        if ($this->socket === false) {
            $this->error = 'not connected';

            return false;
        }

        fclose($this->socket);
        $this->socket = false;

        return true;
    }

    /**
     * Queries searchd status.
     *
     * @return array|false status variable name and value pairs, false on error
     */
    public function status()
    {
        $this->mbPush();
        if (! ($fp = $this->connect())) {
            $this->mbPop();

            return false;
        }

        // len=4, body=1
        $req = pack('nnNN', self::SEARCHD_COMMAND_STATUS, self::VER_COMMAND_STATUS, 4, 1);
        if (! $this->send($fp, $req, 12) || ! ($response = $this->getResponse($fp, self::VER_COMMAND_STATUS))) {
            $this->mbPop();

            return false;
        }

        // just ignore length, error handling, etc
        $res = substr($response, 4);
        $p = 0;
        [$rows, $cols] = array_values(unpack('N*N*', substr($response, $p, 8)));
        $p += 8;

        $res = [];
        for ($i = 0; $i < $rows; ++$i) {
            for ($j = 0; $j < $cols; ++$j) {
                [, $len] = unpack('N*', substr($response, $p, 4));
                $p += 4;
                $res[$i][] = substr($response, $p, $len);
                $p += $len;
            }
        }

        $this->mbPop();

        return $res;
    }

    /**
     * Forces searchd to flush pending attribute updates to disk, and blocks until completion.
     *
     * @return int flush tag, -1 on error
     */
    public function flushAttributes()
    {
        $this->mbPush();
        if (! ($fp = $this->connect())) {
            $this->mbPop();

            return -1;
        }

        // len=0
        $req = pack('nnN', self::SEARCHD_COMMAND_FLUSHATTRS, self::VER_COMMAND_FLUSHATTRS, 0);
        if (! $this->send($fp, $req, 8) || ! ($response = $this->getResponse($fp, self::VER_COMMAND_FLUSHATTRS))) {
            $this->mbPop();

            return -1;
        }

        $tag = -1;
        if (strlen($response) === 4) {
            [, $tag] = unpack('N*', $response);
        } else {
            $this->error = 'unexpected response length';
        }

        $this->mbPop();

        return $tag;
    }

    /**
     * Pack 64-bit signed.
     *
     * @param int|string $v
     *
     * @return string
     * @throws InvalidArgumentException When $v is not numeric
     */
    public function packI64($v)
    {
        if (! is_numeric($v)) {
            throw new InvalidArgumentException('Input must be numeric.');
        }

        // x64
        if (PHP_INT_SIZE >= 8) {
            $v = (int) $v;

            return pack('NN', $v >> 32, $v & 0xFFFFFFFF);
        }

        // x32, int
        if (is_int($v)) {
            return pack('NN', $v < 0 ? -1 : 0, $v);
        }

        // x32, bcmath
        if (function_exists('bcmul')) {
            if (bccomp($v, '0') == -1) {
                $v = bcadd('18446744073709551616', $v);
            }

            $h = bcdiv($v, '4294967296', 0);
            $l = bcmod($v, '4294967296');

            // conversion to float is intentional; int would lose 31st bit
            return pack('NN', (float) $h, (float) $l);
        }

        // x32, no-bcmath
        $p = max(0, strlen($v) - 13);
        $lo = abs((float) substr($v, $p));
        $hi = abs((float) substr($v, 0, $p));

        // (10 ^ 13) % (1 << 32) = 1316134912
        $m = $lo + $hi * 1316134912.0;
        $q = floor($m / 4294967296.0);
        $l = $m - ($q * 4294967296.0);
        // (10 ^ 13) / (1 << 32) = 2328
        $h = $hi * 2328.0 + $q;

        if ($v < 0) {
            if ($l == 0) {
                $h = 4294967296.0 - $h;
            } else {
                $h = 4294967295.0 - $h;
                $l = 4294967296.0 - $l;
            }
        }

        return pack('NN', $h, $l);
    }

    /**
     * Pack 64-bit unsigned.
     *
     * @param int|string $v
     *
     * @return string
     * @throws InvalidArgumentException When $v is not numeric
     */
    public function packU64($v)
    {
        if (! is_numeric($v)) {
            throw new InvalidArgumentException('Input must be numeric.');
        }

        // x64
        if (PHP_INT_SIZE >= 8) {
            if ($v < 0) {
                throw new InvalidArgumentException('Input must be positive.');
            }

            // x64, int
            if (is_int($v)) {
                return pack('NN', $v >> 32, $v & 0xFFFFFFFF);
            }

            // x64, bcmath
            if (function_exists('bcmul')) {
                $h = bcdiv($v, '4294967296', 0);
                $l = bcmod($v, '4294967296');

                return pack('NN', $h, $l);
            }

            // x64, no-bcmath
            $p = max(0, strlen($v) - 13);
            $lo = (int) substr($v, $p);
            $hi = (int) substr($v, 0, $p);

            $m = $lo + $hi * 1316134912;
            $l = $m % 4294967296;
            $h = $hi * 2328 + (int) ($m / 4294967296);

            return pack('NN', $h, $l);
        }

        // x32, int
        if (is_int($v)) {
            return pack('NN', 0, $v);
        }

        // x32, bcmath
        if (function_exists('bcmul')) {
            $h = bcdiv($v, '4294967296', 0);
            $l = bcmod($v, '4294967296');

            // conversion to float is intentional; int would lose 31st bit
            return pack('NN', (float) $h, (float) $l);
        }

        // x32, no-bcmath
        $p = max(0, strlen($v) - 13);
        $lo = (float) substr($v, $p);
        $hi = (float) substr($v, 0, $p);

        $m = $lo + $hi * 1316134912.0;
        $q = floor($m / 4294967296.0);
        $l = $m - ($q * 4294967296.0);
        $h = $hi * 2328.0 + $q;

        return pack('NN', $h, $l);
    }

    /**
     * Unpack 64-bit unsigned.
     *
     * @param string $v
     *
     * @return int|string
     */
    public function unpackU64($v)
    {
        [$hi, $lo] = array_values(unpack('N*N*', $v));

        if (PHP_INT_SIZE >= 8) {
            if ($hi < 0) {
                // because php 5.2.2 to 5.2.5 is totally fucked up again
                $hi += (1 << 32);
            }

            if ($lo < 0) {
                $lo += (1 << 32);
            }

            // x64, int
            if ($hi <= 2147483647) {
                return ($hi << 32) + $lo;
            }

            // x64, bcmath
            if (function_exists('bcmul')) {
                return bcadd($lo, bcmul($hi, '4294967296'));
            }

            // x64, no-bcmath
            $c = 100000;
            $h = ((int) ($hi / $c) << 32) + (int) ($lo / $c);
            $l = (($hi % $c) << 32) + ($lo % $c);
            if ($l > $c) {
                $h += (int) ($l / $c);
                $l = $l % $c;
            }

            if ($h == 0) {
                return $l;
            }

            return sprintf('%d%05d', $h, $l);
        }

        // x32, int
        if ($hi == 0) {
            if ($lo > 0) {
                return $lo;
            }

            return sprintf('%u', $lo);
        }

        $hi = sprintf('%u', $hi);
        $lo = sprintf('%u', $lo);

        // x32, bcmath
        if (function_exists('bcmul')) {
            return bcadd($lo, bcmul($hi, '4294967296'));
        }

        // x32, no-bcmath
        $hi = (float) $hi;
        $lo = (float) $lo;

        $q = floor($hi / 10000000.0);
        $r = $hi - $q * 10000000.0;
        $m = $lo + $r * 4967296.0;
        $mq = floor($m / 10000000.0);
        $l = $m - $mq * 10000000.0;
        $h = $q * 4294967296.0 + $r * 429.0 + $mq;

        $h = sprintf('%.0f', $h);
        $l = sprintf('%07.0f', $l);
        if ($h == '0') {
            return sprintf('%.0f', (float) $l);
        }

        return $h . $l;
    }

    /**
     * Unpack 64-bit signed.
     *
     * @param string $v
     *
     * @return int|string
     */
    public function unpackI64($v)
    {
        [$hi, $lo] = array_values(unpack('N*N*', $v));

        // x64
        if (PHP_INT_SIZE >= 8) {
            if ($hi < 0) {
                // because php 5.2.2 to 5.2.5 is totally fucked up again
                $hi += (1 << 32);
            }

            if ($lo < 0) {
                $lo += (1 << 32);
            }

            return ($hi << 32) + $lo;
        }

        // x32, int
        if ($hi == 0) {
            if ($lo > 0) {
                return $lo;
            }

            return sprintf('%u', $lo);
        }
        if ($hi == -1) {
            // x32, int
            if ($lo < 0) {
                return $lo;
            }

            return sprintf('%.0f', $lo - 4294967296.0);
        }

        $neg = '';
        $c = 0;
        if ($hi < 0) {
            $hi = ~$hi; // @phpstan-ignore-line Unary operation "~" on mixed results in an error.
            $lo = ~$lo; // @phpstan-ignore-line Unary operation "~" on mixed results in an error.
            $c = 1;
            $neg = '-';
        }

        $hi = sprintf('%u', $hi);
        $lo = sprintf('%u', $lo);

        // x32, bcmath
        if (function_exists('bcmul')) {
            return $neg . bcadd(bcadd($lo, bcmul($hi, '4294967296')), (string) $c);
        }

        // x32, no-bcmath
        $hi = (float) $hi;
        $lo = (float) $lo;

        $q = floor($hi / 10000000.0);
        $r = $hi - $q * 10000000.0;
        $m = $lo + $r * 4967296.0;
        $mq = floor($m / 10000000.0);
        $l = $m - $mq * 10000000.0 + $c;
        $h = $q * 4294967296.0 + $r * 429.0 + $mq;
        if ($l == 10000000) {
            $l = 0;
            ++$h;
        }

        $h = sprintf('%.0f', $h);
        $l = sprintf('%07.0f', $l);
        if ($h == '0') {
            return $neg . sprintf('%.0f', (float) $l);
        }

        return $neg . $h . $l;
    }

    /**
     * Fix broken unsigned int.
     *
     * @param int $value
     *
     * @return int|string
     */
    public function fixUint($value)
    {
        if (PHP_INT_SIZE >= 8) {
            // x64 route, workaround broken unpack() in 5.2.2+
            if ($value < 0) {
                $value += (1 << 32);
            }

            return $value;
        }
        // x32 route, workaround php signed/unsigned braindamage
        return sprintf('%u', $value);
    }

    /**
     * Write message to socket.
     *
     * @param resource $handle
     * @param string $data
     * @param int $length
     *
     * @return bool
     */
    private function send($handle, $data, $length)
    {
        if (feof($handle) || fwrite($handle, $data, $length) !== $length) {
            $this->error = 'connection unexpectedly closed (timed out?)';
            $this->connError = true;

            return false;
        }

        return true;
    }

    /**
     * Enter mbstring workaround mode, when function overloading is enabled.
     */
    private function mbPush()
    {
        $this->mbenc = '';

        if (((int) ini_get('mbstring.func_overload')) & 2) {
            $this->mbenc = mb_internal_encoding();
            mb_internal_encoding('latin1');
        }
    }

    /**
     * Leave mbstring workaround mode.
     */
    private function mbPop()
    {
        if ($this->mbenc) {
            mb_internal_encoding($this->mbenc);
        }
    }

    /**
     * Connect to searchd server.
     *
     * @return resource|false
     */
    private function connect()
    {
        if ($this->socket !== false) {
            // we are in persistent connection mode, so we have a socket
            // however, need to check whether it's still alive
            if (! @feof($this->socket)) {
                return $this->socket;
            }

            // force reopen
            $this->socket = false;
        }

        $errno = 0;
        $errstr = '';
        $this->connError = false;

        if ($this->path) {
            $host = $this->path;
            $port = 0;
        } else {
            $host = $this->host;
            $port = $this->port;
        }

        if ($this->timeout <= 0) {
            $fp = @fsockopen($host, $port, $errno, $errstr);
        } else {
            $fp = @fsockopen($host, $port, $errno, $errstr, $this->timeout);
        }

        if (! $fp) {
            if ($this->path) {
                $location = $this->path;
            } else {
                $location = $this->host . ':' . $this->port;
            }

            $errstr = trim($errstr);
            $this->error = sprintf('connection to %s failed (errno=%d, msg=%s)', $location, $errno, $errstr);
            $this->connError = true;

            return false;
        }

        // send client version
        // this is a subtle part. we must do it before (!) reading back from searchd.
        // because otherwise under some conditions (reported on FreeBSD for instance)
        // TCP stack could throttle write-write-read pattern because of Nagle.
        if (! $this->send($fp, pack('N', 1), 4)) {
            fclose($fp);
            $this->error = 'failed to send client protocol version';

            return false;
        }

        // check version
        [, $v] = unpack('N*', fread($fp, 4));
        $v = (int) $v;
        if ($v < 1) {
            fclose($fp);
            $this->error = sprintf('expected searchd protocol version 1+, got version \'%d\'', $v);

            return false;
        }

        return $fp;
    }

    /**
     * Get and check response packet from searchd server.
     *
     * @param resource $fp socket connection
     * @param string|int $clientVer client version in hex
     *
     * @return string|false
     */
    private function getResponse($fp, $clientVer)
    {
        $response = '';
        $len = 0;
        $status = null;
        $ver = '';
        $header = fread($fp, 8);

        if (strlen($header) === 8) {
            [$status, $ver, $len] = array_values(unpack('n2a/Nb', $header));
            $left = $len;
            while ($left > 0 && ! feof($fp)) {
                $chunk = fread($fp, min(8192, $left));
                if ($chunk) {
                    $response .= $chunk;
                    $left -= strlen($chunk);
                }
            }
        }

        if ($this->socket === false) {
            fclose($fp);
        }

        // check response
        $read = strlen($response);
        if (! $response || $read != $len) {
            $this->error = $len
                ? sprintf('failed to read searchd response (status=%d, ver=%d, len=%d, read=%d)', $status, $ver, $len, $read)
                : 'received zero-sized searchd response';

            return false;
        }

        // check status
        if ($status === self::SEARCHD_WARNING) {
            [, $wlen] = unpack('N*', substr($response, 0, 4));
            $this->warning = substr($response, 4, $wlen);

            return substr($response, 4 + $wlen);
        }

        if ($status === self::SEARCHD_ERROR) {
            $this->error = 'searchd error: ' . substr($response, 4);

            return false;
        }

        if ($status === self::SEARCHD_RETRY) {
            $this->error = 'temporary searchd error: ' . substr($response, 4);

            return false;
        }

        if ($status !== self::SEARCHD_OK) {
            $this->error = sprintf('unknown status code \'%d\'', $status);

            return false;
        }

        // check version
        if ($ver < $clientVer) {
            $this->warning = sprintf(
                'searchd command v.%d.%d older than client\'s v.%d.%d, some options might not work',
                $ver >> 8,
                $ver & 0xFF,
                $clientVer >> 8,
                $clientVer & 0xFF
            );
        }

        return $response;
    }

    /**
     * Helper to pack floats in network byte order.
     *
     * @param float $float
     *
     * @return string
     */
    private function packFloat($float)
    {
        // machine order
        $t1 = pack('f', $float);

        // int in machine order
        [, $t2] = unpack('L*', $t1);

        return pack('N', $t2);
    }

    /**
     * Parse and return search query (or queries) response.
     *
     * @param string $response raw response
     * @param int $nreqs number of queries
     *
     * @return array result array
     */
    private function parseSearchResponse($response, $nreqs)
    {
        // current position
        $p = 0;
        // max position for checks, to protect against broken responses
        $max = strlen($response);

        $results = [];
        for ($ires = 0; $ires < $nreqs && $p < $max; ++$ires) {
            $results[] = [];

            $result = &$results[$ires];

            $result['error'] = '';
            $result['warning'] = '';

            // extract status
            [, $status] = unpack('N*', substr($response, $p, 4));
            $p += 4;

            $result['status'] = $status;

            if ($status !== self::SEARCHD_OK) {
                [, $len] = unpack('N*', substr($response, $p, 4));
                $p += 4;

                $message = substr($response, $p, $len);
                $p += $len;

                if ($status === self::SEARCHD_WARNING) {
                    $result['warning'] = $message;
                } else {
                    $result['error'] = $message;
                    continue;
                }
            }

            // read schema
            $fields = [];
            $attrs = [];

            [, $nfields] = unpack('N*', substr($response, $p, 4));
            $p += 4;

            while ($nfields-- > 0 && $p < $max) {
                [, $len] = unpack('N*', substr($response, $p, 4));
                $p += 4;

                $fields[] = substr($response, $p, $len);
                $p += $len;
            }

            $result['fields'] = $fields;

            [, $nattrs] = unpack('N*', substr($response, $p, 4));
            $p += 4;
            while ($nattrs-- > 0 && $p < $max) {
                [, $len] = unpack('N*', substr($response, $p, 4));
                $p += 4;

                $attr = substr($response, $p, $len);
                $p += $len;

                [, $type] = unpack('N*', substr($response, $p, 4));
                $p += 4;

                $attrs[$attr] = $type;
            }

            $result['attrs'] = $attrs;

            // read match count
            [, $count] = unpack('N*', substr($response, $p, 4));
            $p += 4;

            [, $id64] = unpack('N*', substr($response, $p, 4));
            $p += 4;

            // read matches
            $idx = -1;
            while ($count-- > 0 && $p < $max) {
                // index into result array
                ++$idx;

                // parse document id and weight
                if ($id64) {
                    $doc = $this->unpackU64(substr($response, $p, 8));
                    $p += 8;

                    [, $weight] = unpack('N*', substr($response, $p, 4));
                    $p += 4;
                } else {
                    [$doc, $weight] = array_values(unpack('N*N*', substr($response, $p, 8)));
                    $p += 8;

                    $doc = $this->fixUint($doc);
                }

                $weight = sprintf('%u', $weight);

                // create match entry
                if ($this->arrayResult) {
                    $result['matches'][$idx] = ['id' => $doc, 'weight' => $weight];
                } else {
                    $result['matches'][$doc]['weight'] = $weight;
                }

                // parse and create attributes
                $attrvals = [];
                foreach ($attrs as $attr => $type) {
                    // handle 64bit ints
                    if ($type === self::SPH_ATTR_BIGINT) {
                        $attrvals[$attr] = $this->unpackI64(substr($response, $p, 8));
                        $p += 8;
                        continue;
                    }

                    // handle floats
                    if ($type === self::SPH_ATTR_FLOAT) {
                        [, $uval] = unpack('N*', substr($response, $p, 4));
                        $p += 4;

                        [, $fval] = unpack('f*', pack('L', $uval));

                        $attrvals[$attr] = $fval;
                        continue;
                    }

                    // handle everything else as unsigned ints
                    [, $val] = unpack('N*', substr($response, $p, 4));
                    $p += 4;
                    if ($type === self::SPH_ATTR_MULTI) {
                        $attrvals[$attr] = [];
                        $nvalues = $val;
                        while ($nvalues-- > 0 && $p < $max) {
                            [, $val] = unpack('N*', substr($response, $p, 4));
                            $p += 4;

                            $attrvals[$attr][] = $this->fixUint($val);
                        }
                    } elseif ($type === self::SPH_ATTR_MULTI64) {
                        $attrvals[$attr] = [];
                        $nvalues = $val;
                        while ($nvalues > 0 && $p < $max) {
                            $attrvals[$attr][] = $this->unpackI64(substr($response, $p, 8));
                            $p += 8;
                            $nvalues -= 2;
                        }
                    } elseif ($type === self::SPH_ATTR_STRING) {
                        $attrvals[$attr] = substr($response, $p, $val);
                        $p += $val;
                    } else {
                        $attrvals[$attr] = $this->fixUint($val);
                    }
                }

                if ($this->arrayResult) {
                    $result['matches'][$idx]['attrs'] = $attrvals;
                } else {
                    $result['matches'][$doc]['attrs'] = $attrvals;
                }
            }

            [$total, $totalFound, $msecs, $words] = array_values(unpack('N*N*N*N*', substr($response, $p, 16)));
            $result['total'] = sprintf('%u', $total);
            $result['total_found'] = sprintf('%u', $totalFound);
            $result['time'] = sprintf('%.3f', $msecs / 1000);
            $p += 16;

            while ($words-- > 0 && $p < $max) {
                [, $len] = unpack('N*', substr($response, $p, 4));
                $p += 4;

                $word = substr($response, $p, $len);
                $p += $len;

                [$docs, $hits] = array_values(unpack('N*N*', substr($response, $p, 8)));
                $p += 8;

                $result['words'][$word] = [
                    'docs' => sprintf('%u', $docs),
                    'hits' => sprintf('%u', $hits),
                ];
            }
        }

        $this->mbPop();

        return $results;
    }
}
