import { Component, ElementRef, HostListener, OnInit, ViewChild } from '@angular/core';
import { INgxMyDpOptions } from 'ngx-mydatepicker';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { BusinessService } from '../../../../services/business.service';
import { ToastrService } from 'ngx-toastr';
import { SharedService } from '../../../../services/shared.service';
import { articles } from '../../../../constants/articles.const';
import { IMultiSelectOption, IMultiSelectSettings, IMultiSelectTexts } from 'angular-2-dropdown-multiselect';
import { ActivatedRoute, Router } from '@angular/router';
import { BusinessJobFullDetails } from '../../../../../entities/models';
import { closureDateValidator } from '../../../../validators/custom.validator';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { industry } from '../../../../constants/industry.const';

@Component({
  selector: 'app-business-edit-job',
  templateUrl: './business-edit-job.component.html',
  styleUrls: ['./business-edit-job.component.scss']
})
export class BusinessEditJobComponent implements OnInit {
  @ViewChild('content') private content : ElementRef;
  public myOptions: INgxMyDpOptions = { dateFormat: 'yyyy/mm/dd' };
  public businessJobsForm: FormGroup;
  public specifiedBusinessJob: BusinessJobFullDetails;
  public articles = articles;
  public businessJobObject: object;
  public candidatesMatchingCriteria = 0;
  public articlesFirmPredefined: string[] = [];
  public articlesFirmSelectedName: string[];
  public articlesFirmSettings: IMultiSelectSettings = {
    displayAllSelectedText: true,
    selectionLimit: 0,
    showCheckAll: true,
    showUncheckAll: true,
  };
  public currentBusineesJobId: number;
  public articlesFirmOptions: IMultiSelectOption[] = [];
  public articlesFirmTextConfig: IMultiSelectTexts = {
    checkAll: 'Select all',
    uncheckAll: 'Deselect all',
    checked: 'item selected',
    checkedPlural: 'items selected',
    defaultTitle: 'Articles firm',
    allSelected: 'Articles firm - All selected',
  };
  public preloaderPage = true;

  public urlRedirect: string;
  public modalActiveClose: any;

  public articlesFirmTextConfigBus: IMultiSelectTexts = {
    checkAll: 'Select all',
    uncheckAll: 'Deselect all',
    checked: 'item selected',
    checkedPlural: 'items selected',
    defaultTitle: 'Industry',
    allSelected: 'All selected',
  };
  public articlesFirmSettingsBus: IMultiSelectSettings = {
    displayAllSelectedText: true,
    selectionLimit: 0,
    showCheckAll: true,
    showUncheckAll: true,
  };
  public optionsModelBus: string[];
  public indistrySelect: IMultiSelectOption[] = industry;
  public specFilesArray = [];
  public checkSpecFiles = false;

  constructor(
      private readonly _formBuilder: FormBuilder,
      private readonly _businessService: BusinessService,
      private readonly _toastr: ToastrService,
      private readonly _sharedService: SharedService,
      private readonly _route: ActivatedRoute,
      private readonly _router: Router,
      private readonly _modalService: NgbModal
  ) {
    this._sharedService.checkSidebar = false;
  }

  ngOnInit() {
    window.scrollTo(0, 0);
    this.currentBusineesJobId = this._route.snapshot.params.id;
    this.articles.forEach((article) => {
      this.articlesFirmOptions.push({ id: article, name: article });
    });
    this.businessJobObject = new BusinessJobFullDetails({});
    this.createJobsForm();
    this._businessService.getBusinessJobById(this.currentBusineesJobId).then(
      (response) => {
        this.specifiedBusinessJob = response;
        if (this.specifiedBusinessJob['spec']) {
          this.specFilesArray.push({
            name: this.specifiedBusinessJob['spec'].name
          });
        }
        if (this.specifiedBusinessJob.qualification === 0) {
          this.specifiedBusinessJob.qualification = 1;
        }
        setTimeout(()=>{
          this.fetchCandidatesByCriteria(
            this.specifiedBusinessJob.articlesFirm,
            this.specifiedBusinessJob.gender,
            this.specifiedBusinessJob.qualification,
            this.specifiedBusinessJob.nationality,
            this.specifiedBusinessJob.ethnicity,
            this.specifiedBusinessJob.video,
            this.specifiedBusinessJob.location,
            this.specifiedBusinessJob.availability
          );
        }, 200);
        this.businessJobsForm.controls['closureDate'].setValidators([
          Validators.required,
          closureDateValidator(new Date())
        ]);
        this.populateFormWithData();
        this._sharedService.fetchGoogleAutocompleteDetails(this.businessJobsForm);
        this.preloaderPage = false;
      }
    ).catch((err) => {
      if (err.status === 403){
        this.businessJobsForm.reset();
      }
      else if (err.status === 401){
        this.businessJobsForm.reset();
      }
      else {
        this._sharedService.showRequestErrors(err);
      }
    });
  }

  @HostListener('window:beforeunload')
  onBeforeUnload() {
    if (this.businessJobsForm.dirty === true && this.businessJobsForm.touched === true) {
      const confirmTest = "Are you sure you want to leave now?";
      window.event.returnValue = false;
      return confirmTest;
    }
  }

  canDeactivate(url) {
    this.urlRedirect = url;
    if (this.businessJobsForm.dirty === true && this.businessJobsForm.touched === true) {
      this.openVerticallyCentered(this.content);
    }
    else {
      return true;
    }

  }

  /**
   * Upload files
   * @param fieldName {string}
   * @param event {File}
   */
  public async uploadFiles(fieldName: string, event) {
    for (let item of event.target.files){
      this.specFilesArray = [];

      this[fieldName].push(item);

      const formData = new FormData();

      for (let i = 0; i < this.specFilesArray.length; i++) {
        formData.append('spec', this.specFilesArray[i]);
      }

      /*const data = {
        spec: this.specFilesArray[0].name
      };*/

      try {
        const response = await this._businessService.uploadBusinessJobSpec(this.currentBusineesJobId, formData);
        console.log(response);
      }
      catch (err) {
        this._sharedService.showRequestErrors(err);
      }
    }
  }

  /**
   * Remove file
   * @param fieldName {string}
   * @param index {number}
   * @return {Promise<void>}
   */
  public async removeFile(fieldName: string, index: number): Promise<void> {
    this.specFilesArray = [];
    try {
      const data = await this._businessService.deleteBusinessJobSpec(this.currentBusineesJobId);
      console.log(data);
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Exit add job page
   */
  exitPage(){
    this.modalActiveClose.dismiss();
    this.businessJobsForm.reset();
    this._router.navigate([this.urlRedirect]);
  }

  /**
   * creates businessJobsForm and populates with specified data
   * @returns void
   */
  public createJobsForm(): void {
    this.businessJobsForm = this._formBuilder.group({
      jobTitle: [null, Validators.required],
      industry: [null, Validators.required],
      companyName: [null, Validators.required],
      address: [null, Validators.required],
      addressCountry: [null],
      addressState: [null],
      addressZipCode: [null],
      addressCity: [null],
      addressSuburb: [null],
      addressStreet: [null],
      addressStreetNumber: [null],
      addressBuildName: [null],
      addressUnit: [null],
      companyDescription: [null, Validators.compose([
        Validators.required,
        Validators.maxLength(300),
      ])],
      roleDescription: [null, Validators.compose([
        Validators.required,
        Validators.maxLength(400),
      ])],
      closureDate: [null, null],
      started: [null, Validators.required],
      filled: [null, null],
      articlesFirm: [null, Validators.required],
      gender: [null, Validators.required],
      ethnicity: [null, Validators.required],
      nationality: [null, Validators.required],
      // video: [null, Validators.required],
      availability: [null, Validators.required],
      location: [null, Validators.required],
      // post_articles: [null, ''],
      // salary_range: [null, ''],
      qualification: [1, Validators.required],
    });
  }

  /**
   * populates form with data
   * @returns void
   */
  public populateFormWithData(): void {
    this.businessJobsForm.controls['jobTitle'].setValue(this.specifiedBusinessJob.jobTitle);
    this.businessJobsForm.controls['industry'].setValue(this.specifiedBusinessJob.industry);
    this.businessJobsForm.controls['companyName'].setValue(this.specifiedBusinessJob.companyName);
    this.businessJobsForm.controls['address'].setValue(this.specifiedBusinessJob.companyAddress);
    this.businessJobsForm.controls['addressCountry'].setValue(this.specifiedBusinessJob.addressCountry);
    this.businessJobsForm.controls['addressState'].setValue(this.specifiedBusinessJob.addressState);
    this.businessJobsForm.controls['addressCity'].setValue(this.specifiedBusinessJob.addressCity);
    this.businessJobsForm.controls['addressSuburb'].setValue(this.specifiedBusinessJob.addressSuburb);
    this.businessJobsForm.controls['addressZipCode'].setValue(this.specifiedBusinessJob.addressZipCode);
    this.businessJobsForm.controls['addressStreet'].setValue(this.specifiedBusinessJob.addressStreet);
    this.businessJobsForm.controls['addressStreetNumber'].setValue(this.specifiedBusinessJob.addressStreetNumber);
    this.businessJobsForm.controls['addressBuildName'].setValue(this.specifiedBusinessJob.addressBuildName);
    this.businessJobsForm.controls['addressUnit'].setValue(this.specifiedBusinessJob.addressUnit);
    this.businessJobsForm.controls['companyDescription'].setValue(this.specifiedBusinessJob.companyDescription);
    this.businessJobsForm.controls['roleDescription'].setValue(this.specifiedBusinessJob.roleDescription);
    this.businessJobsForm.controls['closureDate'].setValue(this.specifiedBusinessJob.closureDate);
    this.businessJobsForm.controls['started'].setValue(this.specifiedBusinessJob.started);
    this.businessJobsForm.controls['filled'].setValue(this.specifiedBusinessJob.filled);
    const date = new Date(this.specifiedBusinessJob.closureDate);
    this.businessJobsForm.patchValue({closureDate: {
        date: {
            year: date.getFullYear(),
            month: date.getMonth() + 1,
            day: date.getDate(),
        }
    }});
    if (this.specifiedBusinessJob.started) {
      const dateStarted = new Date(this.specifiedBusinessJob.started);
      this.businessJobsForm.patchValue({started: {
        date: {
          year: dateStarted.getFullYear(),
          month: dateStarted.getMonth() + 1,
          day: dateStarted.getDate(),
        }
      }});
    }

    if (this.specifiedBusinessJob.filled) {
      const dateFilled = new Date(this.specifiedBusinessJob.filled);
      this.businessJobsForm.patchValue({filled: {
        date: {
          year: dateFilled.getFullYear(),
          month: dateFilled.getMonth() + 1,
          day: dateFilled.getDate(),
        }
      }});
    }

    if(this.specifiedBusinessJob.articlesFirm.length > 0 && this.specifiedBusinessJob.articlesFirm[0] === "All"){
      this.businessJobsForm.controls['articlesFirm'].setValue(this.articles);
    }
    else{
      this.businessJobsForm.controls['articlesFirm'].setValue(this.specifiedBusinessJob.articlesFirm);
    }
    this.businessJobsForm.controls['gender'].setValue(this.specifiedBusinessJob.gender);
    this.businessJobsForm.controls['ethnicity'].setValue(this.specifiedBusinessJob.ethnicity);
    this.businessJobsForm.controls['nationality'].setValue(this.specifiedBusinessJob.nationality);
    /*this.businessJobsForm.controls['video'].setValue(this.specifiedBusinessJob.video);*/
    this.businessJobsForm.controls['availability'].setValue(this.specifiedBusinessJob.availability);
    this.businessJobsForm.controls['location'].setValue(this.specifiedBusinessJob.location);
    this.businessJobsForm.controls['qualification'].setValue(this.specifiedBusinessJob.qualification);

  }

  /**
   * updates business job specified with id
   * @returns void
   */
  public processJobsEdit(id: number): void {
    if (this.businessJobsForm.valid) {
      Object.keys(this.businessJobsForm.controls).forEach((key) => {
        const fieldName = (key === 'address') ? 'companyAddress' : key;
        switch (key) {
         case 'industry':
             this.businessJobObject[fieldName] = this.businessJobsForm.controls[key].value;
             break;
         case 'nationality':
         /*case 'video':*/
         case 'availability':
         case 'qualification':
         case 'postArticles':
         case 'salaryRange': {
           this.businessJobObject[fieldName] = Number(this.businessJobsForm.controls[key].value);
           break;
         }
         case 'gender': {
           this.businessJobObject[fieldName] = this.businessJobsForm.controls[key].value;
           break;
         }
         case 'ethnicity': {
           this.businessJobObject[fieldName] = this.businessJobsForm.controls[key].value;
           break;
         }
         case 'location': {
           this.businessJobObject[fieldName] = this.businessJobsForm.controls[key].value.label;
           break;
         }
         case 'articlesFirm': {
           this.businessJobObject[fieldName] = this.articlesFirmSelectedName;
           break;
         }
         case 'closureDate': {
           const closureDate = this.businessJobsForm.controls[key].value;
           const year = closureDate.date.year;
           const month = closureDate.date.month;
           const date = (closureDate.date.day.length === 1) ? `0${closureDate.date.day}` : closureDate.date.day;
           this.businessJobObject[fieldName] = `${year}-${month}-${date}`;
           break;
         }
         case 'started': {
           const started = this.businessJobsForm.controls[key].value;
           const year = started.date.year;
           const month = started.date.month;
           const date = (started.date.day.length === 1) ? `0${started.date.day}` : started.date.day;
           this.businessJobObject[fieldName] = `${year}-${month}-${date}`;
           break;
         }
         case 'filled': {
           const filled = this.businessJobsForm.controls[key].value;
           if(filled !== null){
             const year = filled.date.year;
             const month = filled.date.month;
             const date = (filled.date.day.length === 1) ? `0${filled.date.day}` : filled.date.day;
             this.businessJobObject[fieldName] = `${year}-${month}-${date}`;
           }
           else{
               this.businessJobObject[fieldName] = null;
           }
           break;
         }
         default: {
           this.businessJobObject[fieldName] = this.businessJobsForm.controls[key].value;
           break;
         }
        }
      });
      this.businessJobObject['location'] = this.businessJobsForm.controls.location.value;
      this._businessService.updateBusinessJob(id, this.businessJobObject).then(
        (response) => {
          this._toastr.success('Business Job has been successfully updated!');
          this.businessJobsForm.reset();
          if (this.specifiedBusinessJob.approve === true) {
            this._sharedService.sidebarBusinessBadges.jobAwaiting++;
            this._sharedService.sidebarBusinessBadges.jobApproved--;
          }
          this._router.navigate(['/business/awaiting_job']);
        }
      ).catch((err) => {
        if (err.status === 403){
          this.businessJobsForm.reset();
          window.location.reload();
        }
        else if (err.status === 401){
          this.businessJobsForm.reset();
          window.location.reload();
        }
        else {
          this._sharedService.showRequestErrors(err);
        }
      });
    } else {
      this._sharedService.validateAllFormFields(this.businessJobsForm);
    }
  }

  /**
   * sets articles firm criteria
   * @param event
   * @param gender {string}
   * @param qualification {integer}
   * @param nationality {integer}
   * @param ethnicity {string}
   * @param video {integer}
   * @param location {string}
   * @param availability {integer}
   * @returns void
   */
  public async specifiedArticlesFirmCriteria(event, gender?, qualification?, nationality?, ethnicity?, video?, location?, availability?): Promise<void> {
    if (gender === undefined || gender === null) {
      gender = null;
    }
    else if (gender.label){
      gender = gender.label;
    }

    if (ethnicity === undefined || ethnicity === null) {
      ethnicity = null;
    }
    else if (ethnicity.label){
      ethnicity = ethnicity.label;
    }

    if (location === undefined || location === null) {
      location = null;
    }
    else if (location.label){
      location = location.label;
    }

    if (nationality === undefined || nationality === null) {
      nationality = null;
    }
    if (qualification === undefined || qualification === null) {
      qualification = null;
    }
    if (video === undefined || video === null) {
      video = null;
    }
    if (availability === undefined || availability === null) {
      availability = null;
    }
    this.articlesFirmSelectedName = [];
    if (this.articlesFirmPredefined !== null){
      this.articlesFirmPredefined.forEach((index,i) => {
        this.articlesFirmSelectedName.push(this.articlesFirmPredefined[i]);
      });
    }
    this.articlesFirmSelectedName = (this.articlesFirmSelectedName.length === this.articles.length)
      ? new Array('All')
      : this.articlesFirmSelectedName;
    const articlesFirm = (this.articlesFirmSelectedName.length > 0) ? this.articlesFirmSelectedName.join(',') : null;
    try {
      const response = await this._businessService.getBusinessCandidatesCount('', articlesFirm, gender, qualification, nationality, ethnicity, video, location, availability);
      this.candidatesMatchingCriteria = response.countCandidate;
    }
    catch (err) {
      if (err.status === 403){
        this.businessJobsForm.reset();
        window.location.reload();
      }
      else if (err.status === 401){
        this.businessJobsForm.reset();
        window.location.reload();
      }
      else {
        this._sharedService.showRequestErrors(err);
      }
    }
  }

  /**
   * fetches candidate by criteria
   * @param articlesFirm {string}
   * @param gender {string}
   * @param qualification {integer}
   * @param nationality {integer}
   * @param ethnicity {string}
   * @param video {integer]
   * @param location {string}
   * @param availability {integer}
   */
  public fetchCandidatesByCriteria(articlesFirm?, gender?, qualification?, nationality?, ethnicity?, video?, location?, availability?): void {

    if (gender === undefined) {
      gender = null;
    }
    else if (gender.label){
      gender = gender.label;
    }

    if (ethnicity === undefined) {
      ethnicity = null;
    }
    else if (ethnicity.label){
      ethnicity = ethnicity.label;
    }
    if (nationality === undefined) {
      nationality = null;
    }
    if (location === undefined) {
      location = null;
    }
    else if (location.label){
      location = location.label;
    }
    if (qualification === undefined) {
      qualification = null;
    }
    if (video === undefined) {
      video = null;
    }
    if (availability === undefined) {
      availability = null;
    }
    this._businessService.getBusinessCandidatesCount(
        '', articlesFirm, gender, qualification, nationality, ethnicity, video, location, availability).then((data) => {
      this.candidatesMatchingCriteria = data.countCandidate;
    }).catch((err) => {
      if (err.status === 403){
        this.businessJobsForm.reset();
        window.location.reload();
      }
      else if (err.status === 401){
        this.businessJobsForm.reset();
        window.location.reload();
      }
      else {
        this._sharedService.showRequestErrors(err);
      }
    });
  }

  /**
   * Open modal
   * @param content
   */
  public openVerticallyCentered(content) {
    this.modalActiveClose = this._modalService.open(content, { centered: true });
  }
}
