<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
    <channel>
        <title><![CDATA[{{ $channel['title'] }}]]></title>
        <link>{{ $channel['link'] }}</link>
        <description><![CDATA[{{ $channel['description'] }}]]></description>
        <language>{{ $channel['language'] }}</language>
@foreach ($items as $item)
        <item>
            <title><![CDATA[{{ $item->title }}]]></title>
            <link>{{ route('posts.show', $item->slug) }}</link>
            <guid>{{ route('posts.show', $item->slug) }}</guid>
            <pubDate>{{ optional($item->published_at)?->toRssString() }}</pubDate>
            <description><![CDATA[{{ $item->excerpt ?: str($item->content)->stripTags()->limit(200) }}]]></description>
        </item>
@endforeach
    </channel>
</rss>
