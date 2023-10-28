<!-- Modal -->
<div class="modal" id="odModel" tabindex="-1" role="dialog" aria-labelledby="odModel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="" id="ODApprovalForm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="odModelLabel">Leave approval
                    </h4>
                    <p class="text-danger">{{ 'Note: This action is not reversable after 60 minutes.' }}</p>
                </div>
                <div class="modal-body">
                    {{-- <input type="checkbox" name="LeaveApproved" id="LeaveApproved" checked> --}}
                    @csrf
                    @method('POST')
                    <div class="form-group">
                        <label for="remark" class="control-label required">Remark</label>
                        <textarea class="form-control" rows="5" autocomplete="off" placeholder="Remark" name="remark"
                            id="remarksForOD" value="{{ old('remark') }}" required></textarea>
                        @error('remark')
                            <span class="text-danger">{{ $errors->first('remark') }}</span>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button id="modelSubmit" type="submit" class="btn btn-primary">Submit</button>
                </div>
        </form>
    </div>
</div>
</div>
