<div>
    @foreach($content_text_array as $text)
        <div>
            {!! $text !!}
        </div>
    @endforeach

        <a href="{!! $url !!}">点击查看详情</a>
</div>