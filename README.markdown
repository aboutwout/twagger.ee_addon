# Twagger

Tagging weblog entries Twitter-style. Adding tags is as simple as prefix a word with a hash (`#`). You can specify per weblog if a field should be harvested for tags. Tags are accesible through a `{tags}` loop in the when displaying entries, very much similar to the `{categories}` loop.

## Info

Developed by Wouter Vervloet, http://baseworks.nl/

This extension makes use of the fabulous [Morphine theme](http://github.com/newism/nsm.morphine.theme "Download the Morphne theme here") made by [Leevi Graham](http://twitter.com/leevigraham "Follow Leevi Graham on Twitter") from [Newism](http://newism.com.au/).

## Usage

After installing and enabling the Twagger extensions, you can choose which weblogs and which fields you want to harvest tags from. This can be done on the extension settings page.

Twagger doesn't require you to learn a new set of tags as it ties right into the weblog module. Displaying tags is very similar to displaying categories.

  {tags}
    {twagger:tag}, 
  {/tags}

### Parameters

I've tried to keep it as simple as possible and used parameters also available in native EE modules.

  limit     => Limits the amount of tags displayed, defaults to 25
  sort      => Sets the sorting of tags, defaults to ASC
  backspace => Remove characters from the last iteration of the tags loop

### Variables

All the variables are prefixed with `twagger:`. This was necessary to prevent any conflicts with the native EE variables. The `{tag}` variable didn't have a need for this, but I did it anyway for the sake of consistency.

  twagger:tag => Text of the tag
  twagger:total_results =>  The total number of tags being displayed
  twagger:count => The "count" out of the current tags being displayed
  twagger:switch => Works identical to the switch tag in the weblog entries loop

## Example

  <section id="articles">
    {exp:weblog:entries}
      <article>
        <h5>{title}</h5>
        <p class="introduction">{summary}</p>
        {body}
        {extended}
        {tags limit='5' sort='desc'}
          {if twagger:count == 1}<ul>{/if}
            <li class="{twagger:switch='odd|even'}">{twagger:count}. {twagger:tag}</li>
          {if twagger:total_results == twagger:count}</ul>{/if}
        {/tags}
      </article>
    {/exp:weblog:entries}
  </section>