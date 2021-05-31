<div class="form-group row">
    <label class="col-md-3 col-form-label">
        {{translate('Job Title')}}
        <span class="text-danger">*</span>
    </label>
    <div class="col-md-9">
        <input type="text" placeholder="{{translate('Job Title')}}" onkeyup="makeSlug(this.value)" id="job_title" name="job_title" value="{{$job->job_title}}" class="form-control" >
            @error('job_title')
 <div class="alert alert-danger">{{ $message }}</div>
            @enderror

     </div>
</div>
<div class="form-group row">
    <label class="col-md-3 col-form-label">{{translate('Slug')}}</label>
    <div class="col-md-9">
        <input type="text" placeholder="{{translate('Slug')}}" name="slug" id="slug" value="{{ $job->slug }}" class="form-control" >
    </div>
</div>

<div class="form-group row" id="category">
    <label class="col-md-3 col-from-label">
        {{translate('Category')}}
        <span class="text-danger">*</span>
    </label>
    <div class="col-md-9">
        <select
            class="form-control aiz-selectpicker"
            name="category_id"
            id="category_id"
            data-live-search="true"
            @if($job->category != null)
            data-selected="{{ $job->category->id }}"
            @endif
        >
            <option value="">select category</option>
            @foreach ($job_category as $category)
            <option value="{{ $category->id }}">
                {{ $category->category_name }}
            </option>
            @endforeach

        </select>
        @error('category_id')
 <div class="alert alert-danger">{{ $message }}</div>
            @enderror
    </div>
</div>

{{-- company --}}
<div class="form-group row">
    <label class="col-md-3 col-form-label">
        {{translate('Company Name')}}
        <span class="text-danger">*</span>
    </label>
    <div class="col-md-9">
        <input type="text" placeholder="{{translate('Enter Your Company Name')}}"  id="company" name="company" value="{{$job->company}}" class="form-control" >
            @error('company')
 <div class="alert alert-danger">{{ $message }}</div>
            @enderror

     </div>
</div>
{{-- end company --}}

  {{-- location --}}
<div class="form-group row" id="location">
    <label class="col-md-3 col-from-label">
        {{translate('Location')}}
        <span class="text-danger">*</span>
    </label>
    <div class="col-md-9">
        <select class="form-control aiz-selectpicker" name="location_id" id="location_id" data-live-search="true" >
            <option value="" disabled>change location</option>
            @foreach ($job_locations as $item )
<option value="{{$item->id}}" {{$job->location_id == $item->id ? 'selected':''}}>{{$item->location}}</option>
            @endforeach

            {{-- <option selected value="Chittagong">Chittagong</option>
            <option value="Sylhet">Sylhet</option>
            <option value="Rajshahi">Rajshahi</option>
            <option value="Barishal">Barishal</option>
            <option value="Khulna">Khulna</option>
            <option value="Rangpur">Rangpur</option> --}}
        </select>
        @error('location')
 <div class="alert alert-danger">{{ $message }}</div>
            @enderror
    </div>
</div>
{{--End location --}}

{{-- education --}}
<div class="form-group row">
    <label class="col-md-3 col-form-label">
        {{translate('educational Status')}}
        <span class="text-danger">*</span>
    </label>
    <div class="col-md-9">
        <input type="text" placeholder="{{translate('Your educational status')}}"  id="education" name="education" value="{{$job->education}}" class="form-control" >
            @error('education')
 <div class="alert alert-danger">{{ $message }}</div>
            @enderror

     </div>
</div>
{{-- end education --}}

{{-- experience --}}
<div class="form-group row">
    <label class="col-md-3 col-form-label">
        {{translate('Experience')}}
        <span class="text-danger">*</span>
    </label>
    <div class="col-md-9">
        <input type="text" placeholder="{{translate('Your experience')}}"  id="experience" name="experience" value="{{$job->experience}}" class="form-control" >
            @error('experience')
 <div class="alert alert-danger">{{ $message }}</div>
            @enderror

     </div>
</div>
{{-- end experience --}}

  {{-- Gender --}}

  <div class="form-group row">
    <label class="col-md-3 col-form-label">
        {{translate('Gender')}}
        <span class="text-danger">*</span>
    </label>
    <div class="col-md-9">
        <div class="custom-control custom-radio">
<input type="radio" {{$job->gender == 1 ? 'checked':''}} class="custom-control-input" id="customControlValidation2" value="1" name="gender" required>
            <label class="custom-control-label" for="customControlValidation2">Male</label>
            <div class="invalid-feedback">Please check one option</div>
          </div>
          <div class="custom-control custom-radio">
<input type="radio" {{$job->gender == 2 ? 'checked':''}} class="custom-control-input" id="customControlValidation3" value="2" name="gender" required>
            <label class="custom-control-label" for="customControlValidation3">Females</label>
            <div class="invalid-feedback">Please check one option</div>
          </div>
          <div class="custom-control custom-radio">
<input type="radio" {{$job->gender == 3 ? 'checked':''}} class="custom-control-input" id="customControlValidation4" value="3" name="gender" required>
            <label class="custom-control-label" for="customControlValidation4">Both's</label>
            <div class="invalid-feedback">Please check one option</div>
          </div>

     </div>
</div>

{{-- end Gender --}}


{{-- salary --}}
<div class="form-group row">
    <label class="col-md-3 col-form-label">
        {{translate('Salary')}}
        <span class="text-danger">*</span>
    </label>
    <div class="col-md-9">
        <input type="text" placeholder="{{translate('Ex: Negotiable or Enter The Amount')}}"  id="salary" name="salary" value="{{$job->salary}}" class="form-control" >
            @error('salary')
 <div class="alert alert-danger">{{ $message }}</div>
            @enderror

     </div>
</div>
{{-- end salary --}}


{{-- age --}}
<div class="form-group row">
    <label class="col-md-3 col-form-label">
        {{translate('Age')}}
        <span class="text-danger">*</span>
    </label>
    <div class="col-md-9">
        <input type="text" placeholder="{{translate('Ex: 22 to 30 years')}}"  id="age" name="age" value="{{$job->age}}" class="form-control" >
            @error('age')
 <div class="alert alert-danger">{{ $message }}</div>
            @enderror

     </div>
</div>
{{-- end age --}}

{{-- vacancy --}}
<div class="form-group row">
    <label class="col-md-3 col-form-label">
        {{translate('vacancy')}}
        <span class="text-danger">*</span>
    </label>
    <div class="col-md-9">
        <input type="text" placeholder="{{translate('vacancy')}}"  id="vacancy" name="vacancy" value="{{$job->vacancy}}" class="form-control" >
            @error('vacancy')
 <div class="alert alert-danger">{{ $message }}</div>
            @enderror

     </div>
</div>
{{-- end vacancy --}}

{{-- Employment Status --}}
<div class="form-group row" id="category">
    <label class="col-md-3 col-from-label">
        {{translate('Employment Status')}}
        <span class="text-danger">*</span>
    </label>
    <div class="col-md-9">
        <select class="form-control aiz-selectpicker" name="employment_status" id="employment_status" data-live-search="true" >
            <option {{$job->employment_status == 'Full Time' ? 'selected':''}} value="Full Time">Full Time</option>
            <option {{$job->employment_status == 'Part Time' ? 'selected':''}} value="Part Time">Part Time</option>

        </select>
        @error('employment_status')
 <div class="alert alert-danger">{{ $message }}</div>
            @enderror
    </div>
</div>
{{-- end Employment Status --}}

{{-- Address --}}
<div class="form-group row">
    <label class="col-md-3 col-form-label">
        {{translate('Address')}}
        <span class="text-danger">*</span>
    </label>
    <div class="col-md-9">
        <input type="text" placeholder="{{translate('1234 Main St')}}"  id="address" name="address" value="{{$job->address}}" class="form-control" >
            @error('address')
 <div class="alert alert-danger">{{ $message }}</div>
            @enderror

     </div>
</div>
{{-- end Address --}}

{{-- Address 2 --}}
<div class="form-group row">
    <label class="col-md-3 col-form-label">
        {{translate('Address 2')}}
        <span class="text-danger"></span>
    </label>
    <div class="col-md-9">
        <input type="text" placeholder="{{translate('Apartment, studio, or floor')}}"  id="address2" name="address2" value="{{$job->address2}}" class="form-control" >
            @error('address2')
 <div class="alert alert-danger">{{ $message }}</div>
            @enderror

     </div>
</div>
{{-- end Address2 --}}

{{-- Location --}}
{{-- <div class="form-group row">
    <label class="col-md-3 col-form-label">
        {{translate('Location')}}
        <span class="text-danger">*</span>
    </label>
    <div class="col-md-9">
        <input type="text" placeholder="{{translate('Location')}}"  id="location_id" name="location_id" value="{{old('location_id')}}" class="form-control" >
            @error('location_id')
 <div class="alert alert-danger">{{ $message }}</div>
            @enderror

     </div>
</div> --}}
{{-- end Location --}}



<div class="form-group row">
    <label class="col-md-3 col-form-label" for="signinSrEmail">
        {{translate('Banner')}}
        <small>(1300x650)</small>
    </label>
    <div class="col-md-9">
        <div class="input-group" data-toggle="aizuploader" data-type="image">
            <div class="input-group-prepend">
                <div class="input-group-text bg-soft-secondary font-weight-medium">
                    {{ translate('Browse')}}
                </div>
            </div>
            <div class="form-control file-amount">{{ translate('Choose File') }}</div>
            <input type="hidden" name="banner" class="selected-files" value="{{ $job->banner }}">
        </div>
        <div class="file-preview box sm">
        </div>
    </div>
</div>

<div class="form-group row">
    <label class="col-md-3 col-form-label">
        {{translate('Short Description')}}
        <span class="text-danger">*</span>
    </label>
    <div class="col-md-9">
        <textarea name="short_description" rows="5" class="form-control">{{ $job->short_description }}</textarea>
        @error('short_description')
 <div class="alert alert-danger">{{ $message }}</div>
            @enderror
    </div>
</div>

<div class="form-group row">
    <label class="col-md-3 col-from-label">
        {{translate('Description')}}
    </label>
    <div class="col-md-9">
        <textarea class="aiz-text-editor" name="job_description">{{ $job->job_description }}</textarea>
        @error('job_description')
 <div class="alert alert-danger">{{ $message }}</div>
            @enderror
    </div>
</div>





<div class="form-group row">
    <label class="col-md-3 col-form-label">{{translate('Meta Title')}}</label>
    <div class="col-md-9">
        <input type="text" class="form-control" name="meta_title" value="{{ $job->meta_title }}" placeholder="{{translate('Meta Title')}}">
    </div>
</div>

<div class="form-group row">
    <label class="col-md-3 col-form-label" for="signinSrEmail">
        {{translate('Meta Image')}}
        <small>(200x200)+</small>
    </label>
    <div class="col-md-9">
        <div class="input-group" data-toggle="aizuploader" data-type="image">
            <div class="input-group-prepend">
                <div class="input-group-text bg-soft-secondary font-weight-medium">
                    {{ translate('Browse')}}
                </div>
            </div>
            <div class="form-control file-amount">{{ translate('Choose File') }}</div>
            <input type="hidden" name="meta_img" class="selected-files" value="{{ $job->meta_img }}">
        </div>
        <div class="file-preview box sm">
        </div>
    </div>
</div>

<div class="form-group row">
    <label class="col-md-3 col-form-label">{{translate('Meta Description')}}</label>
    <div class="col-md-9">
        <textarea name="meta_description" rows="5" class="form-control">{{ $job->meta_description }}</textarea>
    </div>
</div>

<div class="form-group row">
    <label class="col-md-3 col-form-label">
        {{translate('Meta Keywords')}}
    </label>
    <div class="col-md-9">
        <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" value="{{ $job->meta_keywords }}" placeholder="{{translate('Meta Keywords')}}">
    </div>
</div>

<div class="form-group mb-0 text-right">
    <button type="submit" class="btn btn-primary">
        {{translate('Save')}}
    </button>
</div>
