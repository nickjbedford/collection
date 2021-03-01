<?php
	use YetAnother\SwissCollection;
	
	if (!function_exists('sc'))
	{
		/**
		 * Creates a new collection from the values specified.
		 * @param mixed|null $values
		 * @return SwissCollection
		 */
		function sc($values = null): SwissCollection
		{
			return SwissCollection::create($values);
		}
	}
	
	if (!function_exists('swiss'))
	{
		/**
		 * Creates a new collection from the values specified.
		 * @param mixed|null $values
		 * @return SwissCollection
		 */
		function swiss($values = null): SwissCollection
		{
			return SwissCollection::create($values);
		}
	}
