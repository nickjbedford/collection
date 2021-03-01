<?php
	/** @noinspection PhpUnusedParameterInspection */
	/** @noinspection PhpUnused */
	
	namespace YetAnother;
	
	use ArrayAccess;
	use ArrayIterator;
	use Closure;
	use Exception;
	use IteratorAggregate;
	use Traversable;
	
	/**
	 * Represents an array-based collection of items. This collection
	 * provides a chain-based design to easily transform and mutate
	 * arrays of data, whether associative or not.
	 *
	 * Error checking is kept to a minimum to ensure performance is
	 * high, and native PHP functions are used wherever possible. Only
	 * when native functions cannot provide required functionality are
	 * inline array manipulations performed.
	 */
	class SwissCollection implements ArrayAccess, IteratorAggregate
	{
		protected array $values;
		
		/**
		 * Initialises a new collection with an array of items.
		 * @param array $values An array of items to store in the collection.
		 */
		public function __construct(array $values = [])
		{
			$this->values = $values;
		}
		
		/**
		 * Creates a new collection from mixed input. If an array, Traversible object or an
		 * existing Collection is passed, this is used as the source array. If any other value
		 * is passed, it is converted to an array containing that single value.
		 * @param mixed|array|SwissCollection $values Any array, Collection, Traversible or mixed scalar value to convert to an array.
		 * @return self
		 */
		public static function create($values = []): self
		{
			return new self(self::getArrayFromMixed($values));
		}
		
		/**
		 * Converts mixed input into a valid array. If an array, Traversible object or an
		 * existing Collection is passed, the appropriate array value is returned. If any
		 * other value is passed, it is converted to an array containing that single value.
		 * @param mixed|array|SwissCollection $values Any array, Collection, Traversible or mixed scalar value to convert to an array.
		 * @return array
		 */
		public static function getArrayFromMixed($values = []): array
		{
			if (is_array($values))
                return $values;
			
	        else if ($values instanceof self)
	            return $values->values;
	        
	        else if ($values instanceof Traversable)
	            return iterator_to_array($values);
	        
	        return [ $values ];
		}
		
		/**
		 * Returns the PHP array stored in the collection.
		 * @return array
		 */
		public function get(): array
		{
			return $this->values;
		}
		
		/**
		 * Creates a copy of the collection.
		 * @return self
		 */
		public function copy(): self
		{
			return new self($this->values);
		}
		
		/**
		 * Searches for a value in the collection and returns the offset/key,
		 * otherwise false if the value could not be found.
		 * @param mixed $value The value to search for in the collection.
		 * @param bool $strict Whether to also compare the types of the values.
		 * @return int|string|false
		 */
		public function find($value, bool $strict = false)
		{
			return array_search($value, $this->values, $strict);
		}
		
		/**
		 * Determines if the collection contains a value.
		 * @param mixed $value The value to search for in the collection.
		 * @param bool $strict Whether to search for an exact value and type match.
		 * @return bool
		 */
		public function contains($value, bool $strict = false): bool
		{
			return in_array($value, $this->values, $strict);
		}
		
		/**
		 * Determines if the collection contains a key.
		 * @param mixed $key The key to search for in the collection.
		 * @return bool
		 */
		public function containsKey($key): bool
		{
			return array_key_exists($key, $this->values);
		}
		
		/**
		 * Determines if the collection contains a key/value pair.
		 * @param mixed $key The key to search for in the collection.
		 * @param mixed $value The value to search for in the collection.
		 * @param bool $strict Whether to search for an exact value and type match.
		 * @return bool
		 */
		public function containsKeyValue($key, $value, bool $strict = false): bool
		{
			if (array_key_exists($key, $this->values))
				return $strict ?
					$this->values[$key] === $value :
					$this->values[$key] == $value;
			return false;
		}
		
		/**
		 * Gets the first value in the collection. This will reset
		 * the internal pointer of the collection's array. If the
		 * collection is empty, a default value of null is returned.
		 * @param mixed|null $default The value to return if the collection is empty.
		 * @return mixed
		 */
		public function first($default = null)
		{
			return count($this->values) ? reset($this->values) : $default;
		}
		
		/**
		 * Gets the last value in the collection. This will set
		 * the internal pointer of the collection's array to the end.
		 * If the collection is empty, a default value of null is returned.
		 * @param mixed|null $default The value to return if the collection is empty.
		 * @return mixed|null
		 */
		public function last($default = null)
		{
			return count($this->values) ? end($this->values) : $default;
		}
		
		/**
		 * Adds an element onto the collection.
		 * @param mixed $value The value to add.
		 * @return self
		 */
		public function add($value): self
		{
			$this->values[] = $value;
			return $this;
		}
		
		/**
		 * Pushes an element onto end of the collection.
		 * @param mixed $value The value to push.
		 * @return self
		 */
		public function push($value): self
		{
			array_push($this->values, $value);
			return $this;
		}
		
		/**
		 * Pushes an element onto end of the collection.
		 * @param mixed $value The value to push.
		 * @return self
		 */
		public function queue($value): self
		{
			return $this->push($value);
		}
		
		/**
		 * Pops an element off the end of the collection, returning it.
		 * @return mixed
		 */
		public function pop()
		{
			return array_pop($this->values);
		}
		
		/**
		 * Pops an element off the start of the collection, returning it.
		 * @return mixed
		 */
		public function dequeue()
		{
			return array_shift($this->values);
		}
		
		/**
		 * Pushes the last popped element from this collection onto the
		 * end of another collection and returns the moved element.
		 * @param SwissCollection $collection
		 * @return mixed
		 */
		public function popTo(SwissCollection $collection)
		{
			$collection->push($element = $this->pop());
			return $element;
		}
		
		/**
		 * Removes an element from the collection.
		 * @param mixed $offset The offset within the collection to remove the element.
		 * @return self
		 */
		public function remove($offset): self
		{
			unset($this->values[$offset]);
			return $this;
		}
		
		/**
		 * Removes all elements that are null.
		 * @return self
		 */
		public function removeNulls(): self
		{
			return $this->removeIf(fn($value): bool => $value === null);
		}
		
		/**
		 * Removes all elements that are empty.
		 * @return self
		 */
		public function removeEmpty(): self
		{
			return $this->removeIf(fn($value): bool => empty($value));
		}
		
		/**
		 * Removes elements in the collection that match a specific condition.
		 * @param callable|Closure $predicate A callback that determines if an element should be removed.
		 * @return self
		 */
		public function removeIf($predicate): self
		{
			foreach($this->values as $key=>$value)
				if ($predicate($value))
					unset($this->values[$key]);
			return $this;
		}
		
		/**
		 * Removes keyed elements from the collection based on a list of keys to remove.
		 * @param string[] $keys The list of keys to remove from the collection.
		 * @return self
		 */
		public function removeKeys(array $keys): self
		{
			foreach($keys as $key)
				unset($this->values[$key]);
			return $this;
		}
		
		/**
		 * Removes zero or more elements from a specified offset in the collection.
		 * @param int $offset The offset within the collection to remove the element.
		 * @param int $length The number of elements to remove.
		 * @return self
		 */
		public function removeRange(int $offset, int $length): self
		{
			return $this->splice($offset, $length);
		}
		
		/**
		 * Sets or overwrites the key/values pairs in this collection with those
		 * from another collection.
		 * @param array|SwissCollection|mixed $keyValuePairs The array containing key/value pairs to set or overwrite.
		 * @return self
		 */
		public function overwrite($keyValuePairs): self
		{
			$keyValuePairs = self::getArrayFromMixed($keyValuePairs);
			foreach($keyValuePairs as $key=>$value)
				$this->values[$key] = $value;
			return $this;
		}
		
		/**
		 * Inserts one or more values into the collection.
		 * @param int $offset The offset from which to insert the values.
		 * @param mixed|SwissCollection|array $values The values to insert.
		 * @return self
		 */
		public function insert(int $offset, $values): self
		{
			return $this->splice($offset, 0, self::getArrayFromMixed($values));
		}
		
		/**
		 * Appends one or more values onto the end of the collection.
		 * @param mixed|array|SwissCollection|null $values The value to insert.
		 * @return self
		 */
		public function append($values): self
		{
			$values = self::getArrayFromMixed($values);
			$this->values = array_merge($this->values, $values);
			return $this;
		}
		
		/**
		 * Inserts one or more values onto the front of the collection.
		 * @param mixed|array|SwissCollection|null $values The value to insert.
		 * @return self
		 */
		public function prepend($values): self
		{
			$params = array_merge([ &$this->values ], self::getArrayFromMixed($values));
			call_user_func_array('array_unshift', $params);
			return $this;
		}
		
		/**
		 * Returns a new collection containing all values, arrays and Collection
		 * within the collection flattened into a one-dimensional, keyless collection.
		 * @return self
		 */
		public function flatten(): self
		{
			return new self(self::flattenArray($this->values));
		}
		
		/**
		 * Flattens an array recursively.
		 * @param array $array
		 * @return array
		 */
		private static function flattenArray(array $array): array
		{
			$result = [];
			foreach($array as $value)
			{
				if ($value instanceof SwissCollection)
					$value = $value->values;
				else if ($value instanceof Traversable)
					$value = iterator_to_array($value);
				$result = array_merge($result, is_array($value) ? self::flattenArray($value) : [ $value ]);
			}
			return $result;
		}
		
		/**
		 * Splices elements into and out of the collection.
		 * @param int $offset
		 * @param int|null $length
		 * @param array|null $replacement
		 * @return self
		 */
		public function splice(int $offset, ?int $length = null, ?array $replacement = null): self
		{
			array_splice($this->values, $offset, $length, $replacement);
			return $this;
		}
		
		
		/**
		 * Returns a new collection containing the intersection with another value or set of values.
		 * @param mixed|array|SwissCollection $values One or more values to perform the intersection against.
		 * @param bool $preserveKeys Whether to preserve the keys of the original elements.
		 * @return self
		 */
		public function intersect($values, bool $preserveKeys = false): self
		{
			$result = array_intersect($this->values, self::getArrayFromMixed($values));
			return new self($preserveKeys ? $result : array_values($result));
		}
		
		/**
		 * Returns a new collection containing the intersection with another value or set of values,
		 * also performing checks against the keys in the collections.
		 * @param mixed|array|SwissCollection $values One or more values to perform the intersection against.
		 * @param bool $preserveKeys Whether to preserve the keys of the original elements.
		 * @return self
		 */
		public function intersectAssoc($values, bool $preserveKeys = false): self
		{
			$result = array_intersect_assoc($this->values, self::getArrayFromMixed($values));
			return new self($preserveKeys ? $result : array_values($result));
		}
		
		/**
		 * Returns a new collection containing the intersection with another value or set of values,
		 * comparing against keys in the collections only.
		 * @param mixed|array|SwissCollection $values One or more values to perform the intersection against.
		 * @param bool $preserveKeys Whether to preserve the keys of the original elements.
		 * @return self
		 */
		public function intersectKeys($values, bool $preserveKeys = false): self
		{
			$result = array_intersect_key($this->values, self::getArrayFromMixed($values));
			return new self($preserveKeys ? $result : array_values($result));
		}
		
		/**
		 * Returns a new collection, mapping values via a callback.
		 * @param callable|Closure $callback A callback that maps the input to the output values in the collection.
		 * @return self
		 */
		public function map($callback): self
		{
			return new self(array_map($callback, $this->values));
		}
		
		/**
		 * Returns a new collection, mapping values via a callback and removing null values from the result.
		 * @param callable|Closure $callback A callback that maps the input to the output values in the collection.
		 * @return self
		 */
		public function maybeMap($callback): self
		{
			return $this->map($callback)->removeNulls();
		}
		
		/**
		 * Returns a copy of the collection with only the specified key/value pairs.
		 * @param string[] $keys The list of keys to keep in the copy of the collection.
		 * @return self
		 */
		public function filterToKeys(array $keys): self
		{
			return $this->filter(fn($value, $key): bool => in_array($key, $keys), true);
		}
		
		/**
		 * Returns a new collection, filtered using a predicate.
		 * @param callable|Closure $predicate A callback that determines if an element should be included in the result.
		 * @param bool $includeKeys Whether to include the keys in the predicate as the second argument.
		 * @return self
		 */
		public function filter($predicate, bool $includeKeys = false): self
		{
			return new self(array_filter($this->values, $predicate, $includeKeys ? ARRAY_FILTER_USE_BOTH : 0));
		}
		
		/**
		 * Returns a new collection, filtered using a predicate. This is an alias of filter().
		 * @param callable|Closure $predicate A callback that determines if an element should be included in the result.
		 * @param bool $includeKeys Whether to include the keys in the predicate as the second argument.
		 * @return self
		 */
		public function where($predicate, bool $includeKeys = false): self
		{
			return $this->filter($predicate, $includeKeys);
		}
		
		/**
		 * Returns a new collection containing only the unique elements in the collection.
		 * @return self
		 */
		public function unique(): self
		{
			return new self(array_unique($this->values, SORT_REGULAR));
		}
		
		/**
		 * Returns a new collection containing only the unique elements in the collection, compared as strings.
		 * @return self
		 */
		public function uniqueAsStrings(): self
		{
			return new self(array_unique($this->values, SORT_STRING));
		}
		
		/**
		 * Returns a new collection containing only the unique elements in the collection, compared as strings.
		 * @return self
		 */
		public function uniqueAsNumbers(): self
		{
			return new self(array_unique($this->values, SORT_NUMERIC));
		}
		
		/**
		 * Returns a new collection containing a single property of each element in the collection.
		 * @param string $key The array key or object property name to pluck from the element.
		 * @return self
		 */
		public function pluck($key = 'ID'): self
		{
			return $this->map(fn($item) => is_object($item) ? $item->{$key} : $item[$key]);
		}
		
		/**
		 * Returns a new collection containing associative array elements created from
		 * the specified properties in each element. This will differentiate between
		 * object and array elements but will only create associative arrays in the output.
		 * @param string[]|string $properties The names of the properties in each element to select.
		 * @param bool $preserveKeys Whether to preserve the array's keys.
		 * @return self
		 */
		public function select($properties, bool $preserveKeys = false): self
		{
			$properties = self::getArrayFromMixed($properties);
			$mapper = function($value) use($properties)
			{
				$out = [];
				if (is_object($value))
					foreach($properties as $key)
						$out[$key] = $value->{$key};
				else if (is_array($value))
					foreach($properties as $key)
						$out[$key] = $value[$key];
				else
					throw new Exception('Element in collection is not an array or object.');
				return $out;
			};
			return $preserveKeys ?
				$this->mapAssoc(fn($key, $value) => [ $key, $mapper($value) ]) :
				$this->map(fn($value) => $mapper($value));
		}
		
		/**
		 * Returns a new collection containing a number of elements from the collection
		 * offset from the beginning, optionally limited to a count of elements.
		 * @param int $offset The offset to limit the collection from.
		 * @param int|null $count Optional, the number of elements to limit the new collection to.
		 * @param bool $preserveKeys Whether to preserve the keys in the new collection.
		 * @return self
		 */
		public function offset(int $offset = 0, ?int $count = null, bool $preserveKeys = false): self
		{
			return new self(array_slice($this->values, $offset, $count, $preserveKeys));
		}
		
		/**
		 * Returns a new collection containing a number of elements from the collection,
		 * optionally offset from the beginning.
		 * @param int $count The number of elements to limit the new collection to.
		 * @param int $offset The offset to limit the collection from.
		 * @param bool $preserveKeys Whether to preserve the keys in the new collection.
		 * @return self
		 */
		public function limit(int $count = 1, int $offset = 0, bool $preserveKeys = false): self
		{
			return new self(array_slice($this->values, $offset, $count, $preserveKeys));
		}
		
		/**
		 * Returns a new collection containing a number of elements from the collection
		 * offset from the beginning, optionally limited to a count of elements.
		 * @param int $offset The offset to limit the collection from.
		 * @param int|null $count Optional, the number of elements to limit the new collection to.
		 * @param bool $preserveKeys Whether to preserve the keys in the new collection.
		 * @return self
		 */
		public function slice(int $offset = 0, ?int $count = null, bool $preserveKeys = false): self
		{
			return $this->offset($offset, $count, $preserveKeys);
		}
		
		/**
		 * Determines if all of the elements in the collection match a predicate.
		 * @param callable|Closure $predicate The callback to determine if an element matches a condition.
		 * @return bool
		 */
		public function allMatch($predicate): bool
		{
			return $this->fold(fn($any, $value): bool => $any && boolval($predicate($value)), true);
		}
		
		/**
		 * Determines if any of the elements in the collection match a predicate.
		 * @param callable|Closure $predicate The callback to determine if an element matches a condition.
		 * @return bool
		 */
		public function anyMatch($predicate): bool
		{
			return $this->fold(fn($any, $value): bool => $any || boolval($predicate($value)), false);
		}
		
		/**
		 * Determines if none of the elements in the collection match a predicate.
		 * @param callable|Closure $predicate The callback to determine if an element matches a condition.
		 * @return bool
		 */
		public function noneMatch($predicate): bool
		{
			return !$this->anyMatch($predicate);
		}
		
		/**
		 * Reduces the collection to a single output using an initial value.
		 * @param callable|Closure $accumulator A callback that maps the input to the output values in the collection.
		 * @param mixed $initial The initial value to provide to the callback.
		 * @return mixed
		 */
		public function fold($accumulator, $initial)
		{
			return array_reduce($this->values, $accumulator, $initial);
		}
		
		/**
		 * Reduces the collection to a single output. This is different to fold in that the first value
		 * in the collection becomes the initial value for the accumulator.
		 * @param callable|Closure $accumulator A callback that maps the input to the output values in the collection.
		 * @param mixed|null $default The default value to return when the collection is empty.
		 * @return mixed
		 */
		public function reduce($accumulator, $default = null)
		{
			if (empty($this->values))
				return $default;
			
			$reducable = $this->values;
			$initial = array_shift($reducable);
			return array_reduce($reducable, $accumulator, $initial);
		}
		
		/**
		 * Returns a string containing a string representation of all elements
		 * in the same order, with the delimiter string between each element.
		 * @param string $delimiter The delimiter string to insert between each element.
		 * @return string
		 */
		public function join(string $delimiter = ''): string
		{
			return join($delimiter, $this->values);
		}
		
		/**
		 * Returns a string containing a string representation of all elements
		 * in the same order, joined in a natural way using a delimiter and an "and" delimiter
		 * for the final pair.
		 * @param string $delimiter The delimiter string to insert between each element.
		 * @param string $and The "and" delimiter string to use for the final or only pair.
		 * @return string
		 */
		public function naturalJoin(string $delimiter = ', ', string $and = ' and '): string
		{
			$count = count($this->values);
			if ($count < 3)
				return join($and, $this->values);
			$most = array_slice($this->values, 0, $count - 1, true);
  	        return join($and, [ join($delimiter, $most), end($this->values) ]);
		}
		
		/**
		 * Returns a new collection containing the elements of the collection converted to strings.
		 * @return self
		 */
		public function strings(): self
		{
			return $this->map('strval');
		}
		
		/**
		 * Returns a new collection containing the elements of the collection converted to integers.
		 * @return self
		 */
		public function ints(): self
		{
			return $this->map('intval');
		}
		
		/**
		 * Returns a new collection containing the elements of the collection converted to booleans.
		 * @return self
		 */
		public function bools(): self
		{
			return $this->map('boolval');
		}
		
		/**
		 * Returns a new collection containing the elements of the collection converted to booleans.
		 * @return self
		 */
		public function floats(): self
		{
			return $this->map('floatval');
		}
		
		/**
		 * Returns a HTTP query string from the key/values pairs in the collection.
		 * @return string
		 */
		public function toHttpQuery(): string
		{
			return http_build_query($this->values);
		}
		
		/**
		 * Encodes the collection as a JSON string.
		 * @param bool $pretty Whether to format the JSON output for readability.
		 * @param bool $forceObject Whether to force the collection array to be formatted as an object.
		 * @return string
		 */
		public function toJson(bool $pretty = false, bool $forceObject = false): string
		{
			return json_encode($this->values,
				($pretty ? JSON_PRETTY_PRINT : 0) |
				($forceObject ? JSON_FORCE_OBJECT : 0));
		}
		
		/**
		 * Calculates the sum of the elements in the array.
		 * @return float|int
		 */
		public function sum()
		{
			return array_sum($this->values);
		}
		
		/**
		 * Calculates the sum of the elements in the array that match a predicate.
		 * @param callable|Closure $predicate A function used to determine if the element should be included in the sum.
		 * @return float|int
		 */
		public function sumIf($predicate)
		{
			return $this->fold(
				fn($sum, $value) => $predicate($value) ? ($sum + $value) : $sum, 0);
		}
		
		/**
		 * Calculates the average of the elements in the array.
		 * @return float|int
		 */
		public function average()
		{
			if ($this->isEmpty())
				return 0;
			return $this->sum() / $this->count();
		}
		
		/**
		 * Calculates the average of the elements in the array that match a predicate.
		 * @param callable|Closure $predicate A function used to determine if the element should be included in the average.
		 * @return float|int
		 */
		public function averageIf($predicate)
		{
			return $this->filter($predicate)
				->average();
		}
		
		/**
		 * Calculates the average of the elements in the array.
		 * @return float|int
		 */
		public function product()
		{
			return array_product($this->values);
		}
		
		/**
		 * Returns the number of elements in the collection.
		 * @return int
		 */
		public function count(): int
		{
			return count($this->values);
		}
		
		/**
		 * Returns the number of elements in the collection.
		 * @param callable|Closure $predicate A function used to determine if the element should be included in the count.
		 * @return int
		 */
		public function countIf($predicate): int
		{
			return $this->fold(
				fn(int $count, $value): int => $count + ($predicate($value) ? 1 : 0), 0);
		}
		
		/**
		 * Determines if the collection is empty.
		 * @return bool
		 */
		public function isEmpty(): bool
		{
			return count($this->values) == 0;
		}
		
		/**
		 * Determines if the collection has values.
		 * @return bool
		 */
		public function hasItems(): bool
		{
			return !$this->isEmpty();
		}
		
		/**
		 * Cleans an array using a mapping function and filter predicate to remove elements. For example,
		 * this can be used (by default) to create an array of only trimmed, non-empty strings.
		 * @param callable|Closure|string|null $mapper A mapping function to apply before filter elements.
		 * @param callable|Closure|string|null $removePredicate A predicate that determines which elements should be removed.
		 * @return self
		 */
		public function clean($mapper = 'trim', $removePredicate = 'empty'): self
		{
			$result = $this->values;
			
			if ($mapper)
				$result = array_map($mapper, $result);
			
			if ($removePredicate == 'empty')
				$removePredicate = fn($value): bool => !empty($value);
			
			if ($removePredicate)
				$result = array_filter($result, $removePredicate);
			
			return new self($result);
		}
		
		/**
		 * Returns a new collection containing the array values and keys flipped.
		 * @return self
		 */
		public function flip(): self
		{
			return new self(array_flip($this->values));
		}
		
		/**
		 * Applies a callback to each element of the collection.
		 * @param callable|Closure $callback A callback to apply to each element of the collection.
		 * @return self
		 */
		public function each($callback): self
		{
			foreach($this->values as $value)
				$callback($value);
			return $this;
		}
		
		/**
		 * Applies a callback to each element of the collection.
		 * @param callable|Closure $callback A callback to apply to each element of the collection.
		 * The callback should return false to exit the loop prematurely.
		 * @return bool Whether all elements were processed.
		 */
		public function eachUntil($callback): bool
		{
			foreach($this->values as $value)
				if (!$callback($value))
					return false;
			return true;
		}
		
		/**
		 * Applies a callback to each key and value in the collection.
		 * @param callable|Closure $callback A callback to apply to each element of the collection.
		 * @return self
		 */
		public function eachKeyed($callback): self
		{
			foreach($this->values as $key=>$value)
				$callback($key, $value);
			return $this;
		}
		
		/**
		 * Applies a callback to each key and value in the collection.
		 * @param callable|Closure $callback A callback to apply to each element of the collection.
		 * The callback should return false to exit the loop prematurely.
		 * @return bool
		 */
		public function eachKeyedUntil($callback): bool
		{
			foreach($this->values as $key=>$value)
				if (!$callback($key, $value))
					return false;
			return true;
		}
		
		/**
		 * Returns a new collection using a callback function. This function receives both the keys and values.
		 * @param callable|Closure $keyGenerator A callback to generate a new key for each element in the array.
		 * @return self
		 */
		public function rekey($keyGenerator): self
		{
			$result = [];
			foreach($this->values as $key=>$value)
				$result[$keyGenerator($key, $value)] = $value;
			return new self($result);
		}
		
		/**
		 * Returns a new collection with new keys for each element using the value of a key in each array element.
		 * @param string $key The key to use to set the new collection's key for each array.
		 * @return self
		 */
		public function rekeyFromArrays(string $key = 'ID'): self
		{
			$result = [];
			foreach($this->values as $value)
				$result[$value[$key]] = $value;
			return new self($result);
		}
		
		/**
		 * Returns a new collection with new keys for each element using the value of a property in each object.
		 * @param string $property The property name to use to set the new collection's key for each object.
		 * @return self
		 */
		public function rekeyFromObjects(string $property = 'ID'): self
		{
			$result = [];
			foreach($this->values as $value)
				$result[$value->{$property}] = $value;
			return new self($result);
		}
		
		/**
		 * Returns a new collection with each object converted to associative arrays.
		 * @return self
		 */
		public function objectsToArrays(): self
		{
			return $this->map(
				fn($value): array => is_object($value) ? get_object_vars($value) : $value);
		}
		
		/**
		 * Returns a new key/value collection mapped through a key/value pair generator.
		 * @param callable|Closure $keyValueGenerator A callback to generate a new key/value pair for
		 * each element in the array. The key is passed as the first parameter. The result from the
		 * generator should be an array of two elements, the first being the key, the second being the value.
		 * @return self
		 */
		public function mapAssoc($keyValueGenerator): self
		{
			$result = [];
			foreach($this->values as $key=>$value)
			{
				[$newKey, $newValue] = $keyValueGenerator($key, $value);
				$result[$newKey] = $newValue;
			}
			return new self($result);
		}
		
		/**
		 * Returns a new collection containing a grouped collection of the elements, optionally keyed.
		 * @param callable|Closure $groupKeyGenerator A callback to specify the group of each keyed element.
		 * @param bool $preserveKeys Whether to preserve the keys of each element in the group arrays.
		 * @return self
		 */
		public function group($groupKeyGenerator, bool $preserveKeys = true): self
		{
			$result = [];
			foreach($this->values as $key=>$value)
			{
				$group = $groupKeyGenerator($key, $value);
				$result[$group] ??= [];
				$result[$group][$key] = $value;
			}
			return new self($result);
		}
		
		/**
		 * Returns a new collection containing a grouped collection of the elements, optionally keyed.
		 * @param callable|Closure $groupKeyValueGenerator A callback to specify the group, key and value
		 * of each keyed element in the collection. The result from the generator should be an array of
		 * three values, the first being the group's key, the second being the value's key and the third
		 * being the value itself.
		 * @param bool $preserveKeys Whether to preserve the keys of each element in the group arrays.
		 * @param bool $removeNulls Whether to remove null values from each group collection.
		 * @return self
		 */
		public function mappedGroup($groupKeyValueGenerator, bool $preserveKeys = true, bool $removeNulls = false): self
		{
			$result = [];
			foreach($this->values as $key=>$value)
			{
				[$group, $key, $value] = $groupKeyValueGenerator($key, $value);
				
				if ($removeNulls && $value === null)
					continue;
				
				$result[$group] ??= [];
				$result[$group][$key] = $value;
			}
			return new self($result);
		}
		
		/**
		 * Returns a new collection containing the reversed elements in the collection.
		 * @param bool $preserveKeys Whether to preserve the original keys.
		 * @return self
		 */
		public function reverse(bool $preserveKeys = false): self
		{
			return new self(array_reverse($this->values, $preserveKeys));
		}
		
		/**
		 * Returns a new collection containing the array split into chunks. Each chunk is
		 * a standard PHP array, not a collection instance.
		 * @param int $count The number of elements to include in each chunk.
		 * @param bool $preserveKeys Whether to preserve the keys within each chunk.
		 * @return self
		 */
		public function chunk(int $count, bool $preserveKeys = false): self
		{
			return new SwissCollection(array_chunk($this->values, $count, $preserveKeys));
		}
		
		/**
		 * Returns a new collection containing the array split into predicate-defined
		 * chunks. Each chunk is a standard PHP array, not a collection instance.
		 * @param callable|Closure $predicate The callback to determine whether to create a
		 * new chunk (before the current value is processed). It receives both the key and value as parameters.
		 * @param bool $preserveKeys Whether to preserve the keys within each chunk.
		 * @return self
		 */
		public function chunkIf($predicate, bool $preserveKeys = false): self
		{
			$result = [];
			$chunk = [];
			foreach($this->values as $key=>$value)
			{
				if ($predicate($key, $value))
				{
					$result[] = $chunk;
					$chunk = [];
				}
				
				if ($preserveKeys)
					$chunk[$key] = $value;
				else
					$chunk[] = $value;
			}
			$result[] = $chunk;
			return new self($result);
		}
		
		/**
		 * Sorts the elements in the collection, optionally based on a
		 * custom comparison function.
		 * @param bool $asNumbers Whether to sort the elements as numeric values.
		 * @param bool $descending Whether to sort the elements in descending order.
		 * @return self
		 */
		public function sort(bool $asNumbers = false, bool $descending = false): self
		{
			sort($this->values, ($descending ? SORT_DESC : SORT_ASC) | ($asNumbers ? SORT_NUMERIC : 0));
			return $this;
		}
		
		/**
		 * Sorts the elements in the collection using a user-supplied comparison function.
		 * @param callable|Closure $comparer A function to determine the sorting order of the elements.
		 * @return self
		 */
		public function customSort($comparer): self
		{
			usort($this->values, $comparer);
			return $this;
		}
		
		/**
		 * Sorts the elements in the collection, optionally based on a
		 * custom comparison function.
		 * @param bool $asNumbers Whether to sort the keys as numeric values.
		 * @param bool $descending Whether to sort the keys in descending order.
		 * @return self
		 */
		public function keySort(bool $asNumbers = false, bool $descending = false): self
		{
			ksort($this->values, ($descending ? SORT_DESC : SORT_ASC) | ($asNumbers ? SORT_NUMERIC : 0));
			return $this;
		}
		
		/**
		 * Sorts the elements in the collection, optionally based on a
		 * custom comparison function.
		 * @param callable|Closure $comparer A function to determine the sorting order of the keys.
		 * @return self
		 */
		public function customKeySort($comparer): self
		{
			uksort($this->values, $comparer);
			return $this;
		}
		
		/**
		 * Returns a new collection containing the values of this collection, excluding the keys.
		 * @return self
		 */
		public function values(): self
		{
			return new self(array_values($this->values));
		}
		
		/**
		 * Returns a new collection containing the keys of this collection, excluding the values.
		 * @return self
		 */
		public function keys(): self
		{
			return new self(array_keys($this->values));
		}
		
		/**
		 * Whether a offset exists
		 * @link https://php.net/manual/en/arrayaccess.offsetexists.php
		 * @param mixed $offset <p>
		 * An offset to check for.
		 * </p>
		 * @return bool true on success or false on failure.
		 * </p>
		 * <p>
		 * The return value will be casted to boolean if non-boolean was returned.
		 */
		public function offsetExists($offset): bool
		{
			return isset($this->values[$offset]);
		}
		
		/**
		 * Offset to retrieve
		 * @link https://php.net/manual/en/arrayaccess.offsetget.php
		 * @param mixed $offset <p>
		 * The offset to retrieve.
		 * </p>
		 * @return mixed Can return all value types.
		 */
		public function &offsetGet($offset)
		{
			return $this->values[$offset];
		}
		
		/**
		 * Offset to set
		 * @link https://php.net/manual/en/arrayaccess.offsetset.php
		 * @param mixed $offset <p>
		 * The offset to assign the value to.
		 * </p>
		 * @param mixed $value <p>
		 * The value to set.
		 * </p>
		 * @return void
		 */
		public function offsetSet($offset, $value)
		{
			$this->values[$offset] = $value;
		}
		
		/**
		 * Offset to unset
		 * @link https://php.net/manual/en/arrayaccess.offsetunset.php
		 * @param mixed $offset <p>
		 * The offset to unset.
		 * </p>
		 * @return void
		 */
		public function offsetUnset($offset)
		{
			unset($this->values[$offset]);
		}
		
		/**
		 * Gets a collection iterator.
		 * @return Traversable
		 */
		public function getIterator(): Traversable
		{
			return new ArrayIterator($this->values);
		}
	}
