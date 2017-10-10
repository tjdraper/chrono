# Chrono 2.0.0 for ExpressionEngine

Get years and months of channel entries.

## Tag Pair

    {exp:chrono:archive
        channel_id="8"
        category_id="2430"
        limit="4"
    }
        {chrono:year}
        {chrono:year_count}
        {chrono:year_total}
        {chrono:year_total_entries}
        {chrono:month_total}
        {chrono:months}
            {chrono:short_digit}
            {chrono:two_digit}
            {chrono:short}
            {chrono:long}
            {chrono:month_count}
            {chrono:month_total_entries}
        {/chrono:months}
    {exp:chrono:archive}

### Tag pair parameters

#### `channel_id="3|34"`

Defaults to all channels

#### `author_id="34|56"`

Defaults to all authors

#### `category_id="44|2"`

Defaults to all categories

#### `limit="4"`

Defaults to false (no limit)

#### `year_sort="asc"`

Defaults to "desc"

#### `month_sort="asc"`

Defaults to "desc"

#### `show_expired="true"`

Defaults to false

#### `show_future_entries="true"`

Defaults to false

#### `status="custom|other"`

Defaults to open. Also takes the "not" operator.

    status="not open"

### `namespace="archive"`

Defaults to "chrono". All variables are prefixed with the namespace.

### Tag Variables

All examples assume the default namespace of "chrono"

#### `{chrono:year}` (single tag)

The 4-digit year

#### `{chrono:year_count}` (single tag)

This works just like `{count}` in a channel entries tag pair. This is the loop count.

#### `{chrono:year_total}` (single tag)

This works just like `{total_results}` in a channel entries tag pair. This is the total number of years being output by the tag.

#### `{chrono:year_total_entries}` (single tag)

The total number of entries for the year based on your tag parameters.

#### `{chrono:month_total}` (single tag)

This is the total number of months with entries in this year. For instance if February and April are the only months with entries for the current year the tag is outputting, then `{chrono:month_total}` will be `2`.

#### `{chrono:months}` (tag pair)

This tag pair outputs a loop of the months for the current year being output by the tag.

##### Tag Variables for `{chrono:months}`

###### `{chrono:short_digit}` (single tag)

This outputs the month number without the leading zero. Example: `9`

###### `{chrono:two_digit}` (single tag)

This outputs the month number with a leading zero. Example: `09`

###### `{chrono:short}` (single tag)

This outputs the short month name. Example: `Sept`

###### `{chrono:long}` (single tag)

This outputs the full month name. Example: `September`

###### `{chrono:month_count}` (single tag)

This works just like the `{count}` variable in a channel entries loop. It is the current count of your month loop.

##### `{chrono:month_total_entries}` (single tag)

The total number of entries for the month based on your tag parameters.

## License

Copyright 2017 BuzzingPixel, LLC

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
