<div class="btn-group" data-toggle="buttons">
    @foreach($options as $option => $label)
        <label class="btn btn-default btn-sm {{ Request::get('type', 'default') == $option ? 'active' : '' }}">
            <input type="radio" class="raider-type" value="{{ $option }}">{{$label}}
        </label>
    @endforeach
</div>