import { Component, ElementRef, HostListener, OnInit, ViewChild } from '@angular/core';
import { CandidateService } from '../../../../services/candidate.service';
import { FormControl, FormGroup, Validators } from '@angular/forms';
import { SharedService} from '../../../../services/shared.service';
import { ToastrService } from 'ngx-toastr';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { CandidateReferences } from '../../../../../entities/models';
import { Router } from '@angular/router';
import { AdminCandidateProfile } from '../../../../../entities/models-admin';

@Component({
  selector: 'app-candidate-achievements',
  templateUrl: './candidate-achievements.component.html',
  styleUrls: ['./candidate-achievements.component.scss']
})
export class CandidateAchievementsComponent implements OnInit {
  @ViewChild('contentReference') private contentReference: ElementRef;
  @ViewChild('contentAchievement') private contentAchievement: ElementRef;
  @ViewChild('contentAll') private contentAll: ElementRef;

  public candidateProfileDetails: AdminCandidateProfile;

  public progressBar;
  public achievementsForm: FormGroup;
  public achievementsArray = [];
  public achievementsEditForm: FormGroup;
  public achievementsEditId: number;

  public referencesForm: FormGroup;
  public referencesArray = [];
  public referenceObject: CandidateReferences;
  public referencesEditForm: FormGroup;
  public referencesEditId: number;
  public referenceObjectUpdate: CandidateReferences;

  public modalActiveClose: any;

  public preloaderPage = true;
  public checkVideo;
  public allowVideo;
  public permision: boolean;
  public permisionUpdate: boolean;
  public urlRedirect: string;

  public visibilityLooking = false;
  public visibilityVisible = false;
  public checkLooking: boolean;
  public checkVisible: boolean;
  public videoUploadPopup = false;
  public visibleActivePopup = false;
  public permissonPopup = false;
  public checkRequiredPermission = false;

  constructor(
    private readonly _candidateService: CandidateService,
    public readonly _sharedService: SharedService,
    private readonly _toastr: ToastrService,
    private readonly _modalService: NgbModal,
    private readonly _router: Router
  ) {
    this._sharedService.checkSidebar = false;
  }

  ngOnInit() {
    window.scrollTo(0, 0);
    this.progressBar = localStorage.getItem('progressBar');

    this.achievementsForm = new FormGroup({
      description: new FormControl('', [
        Validators.required,
        Validators.maxLength(50)
      ])
    });

    this.referencesForm = new FormGroup({
      firstName: new FormControl('', Validators.required),
      lastName: new FormControl('', Validators.required),
      company: new FormControl('', Validators.required),
      role: new FormControl('', Validators.required),
      email: new FormControl('', Validators.email),
      comment: new FormControl('', Validators.required)
    });

    this.getCandidateAchievement().then(response => {
      this.getCandidateReferences().then(() => {
        this.getCandidateProfile();
      });
    });
  }

  @HostListener('window:beforeunload')
  onBeforeUnload() {
    if (this.achievementsForm.dirty === true &&
        this.achievementsForm.touched === true &&
        this.referencesForm.dirty === true &&
        this.referencesForm.touched === true) {
      const confirmTest = "Are you sure you want to leave now?";
      window.event.returnValue = false;
      return confirmTest;
    } else if (this.referencesForm.dirty === true && this.referencesForm.touched === true) {
      const confirmTest = "Are you sure you want to leave now?";
      window.event.returnValue = false;
      return confirmTest;
    } else if (this.achievementsForm.dirty === true && this.achievementsForm.touched === true) {
      const confirmTest = "Are you sure you want to leave now?";
      window.event.returnValue = false;
      return confirmTest;
    }
  }

  canDeactivate(url) {
    this.urlRedirect = url;
    if (this.achievementsForm.dirty === true &&
        this.achievementsForm.touched === true &&
        this.referencesForm.dirty === true &&
        this.referencesForm.touched === true) {
      this.openVerticallyCenter(this.contentAll);
    } else if (this.referencesForm.dirty === true && this.referencesForm.touched === true) {
      this.openVerticallyCenter(this.contentReference);
    } else if (this.achievementsForm.dirty === true && this.achievementsForm.touched === true) {
      this.openVerticallyCenter(this.contentAchievement);
    }
    else {
      return true;
    }
  }

  /**
   * Exit add job page
   */
  exitPage(){
    this.modalActiveClose.dismiss();
    this.referencesForm.markAsPristine();
    this.achievementsForm.markAsPristine();
    this._router.navigate([this.urlRedirect]);
  }

  public choicePermisionCheck(permision, value) {
    this[permision] = value;
  }

  /**
   * Select change router
   * @param url
   */
  public routerApplicants(url): void {
    this._router.navigate([url]);
  }

  /**
   * Get candidate profile
   * @returns {Promise<void>}
   */
  public async getCandidateProfile(): Promise<any> {
    try {
      const data = await this._candidateService.getCandidateProfileDetails();
      this.checkVideo = data.profile.video;
      this.progressBar = data.profile.percentage;
      localStorage.setItem('progressBar', String(data.profile.percentage));
      this.allowVideo = data['allowVideo'];
      this.candidateProfileDetails = data;
      if(this.candidateProfileDetails.profile.percentage < 50 || !this.candidateProfileDetails.profile.cvFiles ||
        !this.candidateProfileDetails.profile.cvFiles[0] ||
        !this.candidateProfileDetails.profile.cvFiles[0].approved ||
        (this.candidateProfileDetails.allowVideo === false && !this.candidateProfileDetails.profile.video) ||
        (this.candidateProfileDetails.allowVideo === false && this.candidateProfileDetails.profile.video && this.candidateProfileDetails.profile.video.approved === false)) {
        this.checkVisible = false;
        this.checkLooking = false;
        this.visibilityLooking = true;
        this.visibilityVisible = true;
      } else {
        this.checkVisible = this.candidateProfileDetails.profile.visible;
        this.checkLooking = this.candidateProfileDetails.profile.looking;
      }
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }


  /**
   * Change status candidate
   * @param field {string}
   * @param value {boolean}
   */
  public changeStatusCandidate(field: string, value: boolean){
    let error = true;
    if(this.candidateProfileDetails.profile.percentage < 50) {
      this._toastr.error('Your profile needs to be 50% complete');
      error = false;
      if(field === 'looking'){
        this.checkLooking = false;
      }
      else if(field === 'visible'){
        this.checkVisible = false;
      }
    }
    if(!this.candidateProfileDetails.profile.cvFiles || this.candidateProfileDetails.profile.cvFiles.length === 0){
      this._toastr.error('You need to upload CV file');
      error = false;
      if(field === 'looking'){
        this.checkLooking = false;
      }
      else if(field === 'visible'){
        this.checkVisible = false;
      }
    }
    if(this.candidateProfileDetails.profile.cvFiles[0] && !this.candidateProfileDetails.profile.cvFiles[0].approved){
      this._toastr.error('Your CV files is not approved by the administrator');
      error = false;
      if(field === 'looking'){
        this.checkLooking = false;
      }
      else if(field === 'visible'){
        this.checkVisible = false;
      }
    }
    if (!this.candidateProfileDetails.profile.video && this.candidateProfileDetails.allowVideo === false) {
      this._toastr.error('You need to upload video');
      error = false;
      if(field === 'looking'){
        this.checkLooking = false;
      }
      else if(field === 'visible'){
        this.checkVisible = false;
      }
    }
    if (this.candidateProfileDetails.profile.video && !this.candidateProfileDetails.profile.video.approved && this.candidateProfileDetails.allowVideo === false) {
      this._toastr.error('Your video is not approved by the administrator');
      error = false;
      if(field === 'looking'){
        this.checkLooking = false;
      }
      else if(field === 'visible'){
        this.checkVisible = false;
      }
    }
    if(field === 'visible'){
      if(this.checkLooking !== true){
        this._toastr.error('You need to turn ON "I am looking for a job"');
        error = false;
        this.visibilityVisible = true;
      }
      else{
        this.visibilityVisible = false;
      }
    }
    if (error === true) {
      if (field === 'looking' && value === false) {
        this.closeLookingPopup(true, false);
      } else if (field === 'visible' && value === true) {
        this.closeVisiblePopup(true, true);
      } else {
        const data = {[field]:value};

        this._candidateService.changeStatusCandidate(data).then(data => {
          this.checkLooking = data.looking;
          this.checkVisible = data.visible;
          if(this.checkLooking === true){
            this.visibilityVisible = false;
          }
          else{
            this.visibilityVisible = true;
          }
          if (field === 'looking') {
            this._toastr.success('Your profile is now active');
          } else if (field === 'visible' && value === true) {
            this._toastr.success('Your profile is now visible to all employers');
          } else {
            this._toastr.success('Your profile is now not visible to all employers');
          }
        }).catch(err => {
          this._sharedService.showRequestErrors(err);
        });
      }
    }
  }


  /**
   * Status looking popup
   * @param value
   * @param check
   */
  public closeLookingPopup(value, check) {
    this.videoUploadPopup = value;
    this.checkLooking = check;
  }

  /**
   * Status visible popup
   * @param value
   * @param check
   */
  public closeVisiblePopup(value, check) {
    this.visibleActivePopup = value;
    if(check === false) {
      this.checkVisible = false;
    } else {
      this.checkVisible = true;
    }
  }

  /**
   * Send request looking job
   * @param field
   * @param value
   */
  public lookingJobToggle(field: string, value: boolean) {
    this.checkLooking = false;
    const data = {[field]:value};

    this._candidateService.changeStatusCandidate(data).then(data => {
      this.checkLooking = data.looking;
      this.checkVisible = data.visible;
      if(this.checkLooking === true){
        this.visibilityVisible = false;
      }
      else{
        this.visibilityVisible = true;
      }
      this._toastr.error('Your profile is now disabled');
    }).catch(err => {
      this._sharedService.showRequestErrors(err);
    });
  }

  /**
   * Send request visible jobs
   * @param field
   * @param value
   */
  public visibleJobToggle(field: string, value: boolean) {
    this.checkVisible = true;
    const data = { [field]: value };

    this._candidateService.changeStatusCandidate(data).then(data => {
      this.checkVisible = data.visible;
      this._toastr.success('Your profile is now visible to all employers');
    }).catch(err => {
      this._sharedService.showRequestErrors(err);
    });
  }

  /**
   * Step to next page
   */
  public stepNextPage(): void {
    this._router.navigate(['/candidate/video']);
  }

  /**
   * Get candidate achievements
   * @return {Promise<void>}
   */
  public async getCandidateAchievement(): Promise<void> {
    this.achievementsArray = await this._candidateService.getCandidateAchievement();
  }

  /**
   * Create candidate achievements
   * @return {Promise<void>}
   */
  public async createCandidateAchievement(): Promise<void> {
    this._candidateService.createCandidateAchievement(this.achievementsForm.value.description).then(data => {
      this.achievementsArray.unshift(data.achievement);
      this._toastr.success('Achievement has been created');

      this.progressBar = data.percentage;
      this._sharedService.progressBar = data.percentage;
      localStorage.setItem('progressBar', String(data.percentage));
      this.achievementsForm = new FormGroup({
        description: new FormControl('', [
          Validators.required,
          Validators.maxLength(50)
        ])
      });
    }).catch(err => {
      this._sharedService.showRequestErrors(err);
    })
  }

  /**
   * Delete candidate achievements
   * @param id {number}
   * @return {Promise<void>}
   */
  public async deleteCandidateAchievement(id: number): Promise<void> {
    const data = await this._candidateService.deleteCandidateAchievement(id);
    this.progressBar = data.percentage;
    this._sharedService.progressBar = data.percentage;
    localStorage.setItem('progressBar', String(data.percentage));
    this._toastr.success('Achievement has been deleted');

    for (let i = 0; i < this.achievementsArray.length; i++) {
      if(this.achievementsArray[i].id === id ) {
        this.achievementsArray.splice(i, 1);
      }
    }
  }

  /**
   * Get achievement edit
   * @param id {number}
   * @param description {string}
   * @return {Promise<void>}
   */
  public async getAchievementEdit(id: number, description: string): Promise<void> {
    this.achievementsEditForm = new FormGroup({
      description: new FormControl(description, [
        Validators.required,
        Validators.maxLength(50)
      ])
    });
    this.achievementsEditId = id;
  }

  /**
   * Update candidate achievement
   * @return {Promise<void>}
   */
  public async updateCandidateAchievement(): Promise<void> {
    try {
      const data = await this._candidateService.updateCandidateAchievement(this.achievementsEditId, this.achievementsEditForm.value.description);

      const getUpdateAchievements = this.achievementsArray.find(user => user.id === this.achievementsEditId);
      getUpdateAchievements.description = this.achievementsEditForm.value.description;

      this.progressBar = data.percentage;
      localStorage.setItem('progressBar', String(data.percentage));
      this._sharedService.progressBar = data.percentage;

      this.closeActiveModal();

      this._toastr.success('Achievement has been updated');
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Permission value check
   */
  public permisionValue(field, value){
    value = !value;
  }

  /**
   * Get candidate references
   * @return {Promise<void>}
   */
  public async getCandidateReferences(): Promise<void> {
    try {
      this.referencesArray = await this._candidateService.getCandidateReferences();

      this.preloaderPage = false;
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Events permission popup
   * @param value {boolean}
   * @param toggleValue {boolean}
   */
  public eventPermissionPopup(value, toggleValue) {
    this.permissonPopup = value;
    if (toggleValue === true) {
      this.referenceObject.permission = undefined;
      this.permision = undefined;
    }
  }

  /**
   * Second find to add reference
   */
  public subCreateReference() {

    this.referenceObject = {
      firstName: this.referencesForm.value.firstName,
      lastName: this.referencesForm.value.lastName,
      company: this.referencesForm.value.company,
      role: this.referencesForm.value.role,
      email: this.referencesForm.value.email,
      comment: this.referencesForm.value.comment,
      permission: this.permision,
    };

    this._candidateService.createCandidateReferences(this.referenceObject).then(data => {
      this.referencesArray.unshift(data.reference);

      this.progressBar = data.percentage;
      this._sharedService.progressBar = data.percentage;
      localStorage.setItem('progressBar', String(data.percentage));

      this._toastr.success('Reference has been created');

      this.referencesForm = new FormGroup({
        firstName: new FormControl('', Validators.required),
        lastName: new FormControl('', Validators.required),
        company: new FormControl('', Validators.required),
        role: new FormControl('', Validators.required),
        email: new FormControl('', Validators.email),
        comment: new FormControl('', Validators.required)
      });
      this.permision = undefined;
      this.checkRequiredPermission = false;
    }).catch(err => {
      this._sharedService.showRequestErrors(err);
    })
  }

  /**
   * Create candidate references
   */
  public createCandidateReferences() {

    this.referenceObject = {
      firstName: this.referencesForm.value.firstName,
      lastName: this.referencesForm.value.lastName,
      company: this.referencesForm.value.company,
      role: this.referencesForm.value.role,
      email: this.referencesForm.value.email,
      comment: this.referencesForm.value.comment,
      permission: this.permision,
    };

    if (this.referencesForm.valid) {
      if (this.permision === undefined){
        this._toastr.error('Permission field needs to be completed');
        this.checkRequiredPermission = true;
      } else if (this.permision === false) {
        this.permissonPopup = true;
      } else {
        this._candidateService.createCandidateReferences(this.referenceObject).then(data => {
          this.referencesArray.unshift(data.reference);

          this.progressBar = data.percentage;
          this._sharedService.progressBar = data.percentage;
          localStorage.setItem('progressBar', String(data.percentage));

          this._toastr.success('Reference has been created');

          this.referencesForm = new FormGroup({
            firstName: new FormControl('', Validators.required),
            lastName: new FormControl('', Validators.required),
            company: new FormControl('', Validators.required),
            role: new FormControl('', Validators.required),
            email: new FormControl('', Validators.email),
            comment: new FormControl('', Validators.required)
          });
          this.permision = undefined;
          this.checkRequiredPermission = false;
        }).catch(err => {
          this._sharedService.showRequestErrors(err);
        })
      }
    }
    else{
      if (this.permision === undefined){
        this._toastr.error('Permission field needs to be completed');
        this.checkRequiredPermission = true;
      }
      this._sharedService.validateAllFormFields(this.referencesForm);
    }
  }

  /**
   * Delete candidate references
   * @param id {number}
   * @return {Promise<void>}
   */
  public async deleteCandidateReferences(id: number): Promise<void> {
    const data = await this._candidateService.deleteCandidateReferences(id);
    this.progressBar = data.percentage;
    this._sharedService.progressBar = data.percentage;
    localStorage.setItem('progressBar', String(data.percentage));
    this._toastr.success('Reference has been deleted');

    for (let i = 0; i < this.referencesArray.length; i++) {
      if(this.referencesArray[i].id === id ) {
        this.referencesArray.splice(i, 1);
      }
    }
  }

  /**
   * Get references edit
   * @param id {number}
   * @param data {Object}
   * @param permission {string}
   * @return {Promise<void>}
   */
  public async getReferencesEdit(id: number, data, permission: boolean): Promise<void> {
    this.referencesEditForm = new FormGroup({
      firstName: new FormControl(data.firstName, Validators.required),
      lastName: new FormControl(data.lastName, Validators.required),
      company: new FormControl(data.company, Validators.required),
      role: new FormControl(data.role, Validators.required),
      email: new FormControl(data.email, Validators.email),
      comment: new FormControl(data.comment, Validators.required),
      permission: new FormControl(data.permission, Validators.required)
    });
    this.referencesEditId = id;
    this.permisionUpdate = permission;
  }

  /**
   * Update candidate references
   * @return {Promise<void>}
   */
  public async updateCandidateReferences(): Promise<void> {

    this.referenceObjectUpdate = {
      firstName: this.referencesEditForm.value.firstName,
      lastName: this.referencesEditForm.value.lastName,
      company: this.referencesEditForm.value.company,
      role: this.referencesEditForm.value.role,
      email: this.referencesEditForm.value.email,
      comment: this.referencesEditForm.value.comment,
      permission: this.permisionUpdate
    };

    if (this.referencesEditForm.valid) {
      try {
        const data = await this._candidateService.updateCandidateReferences(this.referencesEditId, this.referenceObjectUpdate);

        const getUpdateReferences = this.referencesArray.find(user => user.id === this.referencesEditId);
        getUpdateReferences.firstName = this.referencesEditForm.value.firstName;
        getUpdateReferences.lastName = this.referencesEditForm.value.lastName;
        getUpdateReferences.company = this.referencesEditForm.value.company;
        getUpdateReferences.role = this.referencesEditForm.value.role;
        getUpdateReferences.email = this.referencesEditForm.value.email;
        getUpdateReferences.comment = this.referencesEditForm.value.comment;
        getUpdateReferences.permission = this.referencesEditForm.value.permission;

        this.progressBar = data.percentage;
        this._sharedService.progressBar = data.percentage;
        localStorage.setItem('progressBar', String(data.percentage));

        this.closeActiveModal();

        this._toastr.success('Reference has been updated');
      }
      catch (err) {
        this._sharedService.showRequestErrors(err);
      }
    }

  }

  /**
   * Close modal
   */
  closeActiveModal() {
    this.modalActiveClose.dismiss();
  }

  /**
   * Managed modal
   * @param content {any} - content to be shown in popup
   * @param id {number} - job id to be used for fetching data and showing in popup
   */
  public openVerticallyCentered(content: any,  id: number) {
    this.modalActiveClose = this._modalService.open(content, { centered: true, 'size': 'lg' });
    this.modalActiveClose.result.then(
      (data) => {
        this._sharedService.resetForm(this.achievementsForm);
      },
      (res) => {
        this._sharedService.resetForm(this.achievementsForm);
      });
  }

  /**
   * Managed modal
   * @param content {any} - content to be shown in popup
   */
  public openVerticallyCenter(content: any) {
    this.modalActiveClose = this._modalService.open(content, { centered: true});
  }

}
