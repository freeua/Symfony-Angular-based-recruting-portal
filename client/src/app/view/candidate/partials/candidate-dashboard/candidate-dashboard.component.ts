import { ChangeDetectorRef, Component, ElementRef, OnInit, ViewChild } from '@angular/core';
import { CandidateService } from '../../../../services/candidate.service';
import { SharedService } from '../../../../services/shared.service';
import { ToastrService } from 'ngx-toastr';
import { CandidateDashboard, CandidateInterviewRequest, CandidateOpportunities } from '../../../../../entities/models';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { CookieService } from 'ngx-cookie-service';
import { MapsAPILoader } from '@agm/core';
import { Observable } from 'rxjs/Observable';
import { Router } from '@angular/router';

@Component({
  selector: 'app-candidate-dashboard',
  templateUrl: './candidate-dashboard.component.html',
  styleUrls: ['./candidate-dashboard.component.scss']
})
export class CandidateDashboardComponent implements OnInit {
  @ViewChild('content') private content : ElementRef;
  @ViewChild('contentDisable') private contentDisable : ElementRef;

  public checkLooking: boolean;
  public checkVisible: boolean;
  public videoCheck: any;
  public progressBar: number;
  public nameUser: string;
  public accessSeenJob = {};

  public awaiting: number;
  public successful: number;
  public declined: number;
  public unsuccessful: number;
  public missed: number;
  public view: number;
  public unique: number;
  public play: number;
  public jobAlertsTotal = {
    number: 0
  };
  public interviewRequestTotal = {
    number: 0
  };

  public candidateDashboard = new CandidateDashboard({});
  public jobAlerts = Array<CandidateOpportunities>();
  public interviewRequest = Array<CandidateInterviewRequest>();

  public modalActiveClose: any;

  public preloaderPage = true;
  public checkStatus = false;
  public visibilityLooking = false;
  public visibilityVisible = false;
  public distanceJobAlerts = [];
  public distanceInterviewRequets = [];

  public currentJob: any;
  public statusButtonJob: any;
  public jobArray: any;
  public candidateProfileCV: any;
  public allowVideo: boolean;
  public videoUploadPopup = false;
  public visibleActivePopup = false;

  constructor(
    private readonly _candidateService: CandidateService,
    public readonly _sharedService: SharedService,
    private readonly _toastr: ToastrService,
    private readonly _modalService: NgbModal,
    private readonly _cookieService: CookieService,
    private readonly _mapsAPILoader: MapsAPILoader,
    private readonly _router: Router,
    private readonly _ref: ChangeDetectorRef
  ) {
    this._sharedService.checkSidebar = false;
  }

  ngOnInit() {
    window.scrollTo(0, 0);
    this.setStatusFirstName();
    this._candidateService.getCandidateProfileDetails().then(data => {
      this.candidateProfileCV = data.profile.cvFiles;
      this.checkVisible = data.profile.visible;
      this.checkLooking = data.profile.looking;
      this.videoCheck = data.profile.video;
      this.progressBar = data.profile.percentage;
      this.nameUser = data.user.firstName;

      if(this.progressBar < 50) {
        const session = sessionStorage.getItem('session' + data.user.id);
        if(!session) {
          const session = sessionStorage.setItem('session' + data.user.id, 'true');
          this._router.navigate(['/candidate/profile_details']);
        }
      }

      this.accessSeenJob['videoCheck'] = this.videoCheck;
      this.accessSeenJob['candidateProfileCV'] = this.candidateProfileCV;
      this.accessSeenJob['progressBar'] = this.progressBar;
      this.accessSeenJob['checkLooking'] = this.checkLooking;

      this.getCandidateDashboard().subscribe(data => {
        this.distanceJobAlerts.push(data);
        this._ref.detectChanges();
      });
    });
  }

  /**
   *
   * @return {Promise<void>}
   */
  public async setStatusFirstName(): Promise<void> {
    const id = Number(localStorage.getItem('id'));
    const data = this._cookieService.get('firstLoginCandidate_' + id);
    if (Boolean(data) === false) {
      const dataRouter = this._cookieService.get('firstLoginCandidateRouter_' + id);
      if (Boolean(dataRouter) === true) {
        this._cookieService.set('firstLoginCandidate_' + id, 'true', 365, '/');
      }
      else {
        this._cookieService.set('firstLoginCandidateRouter_' + id, 'true', 365, '/');
        this._router.navigate(['/candidate/profile_details']);
      }
    }
    else {
      this.checkStatus = true;
    }
  }

  /**
   * Change status candidate
   * @param field {string}
   * @param value {boolean}
   * @param video {boolean}
   * @param progress {boolean}
   */
  public changeStatusCandidate(field: string, value: boolean, video, progress){
    let error = true;
    if(progress < 50) {
      this._toastr.error('Your profile needs to be 50% complete');
      error = false;
      if(field === 'looking'){
          this.checkLooking = false;
      }
      else if(field === 'visible'){
          this.checkVisible = false;
      }
    }
    if(!this.candidateProfileCV || this.candidateProfileCV.length === 0){
      this._toastr.error('You need to upload CV file');
      error = false;
      if(field === 'looking'){
          this.checkLooking = false;
      }
      else if(field === 'visible'){
          this.checkVisible = false;
      }
    }
    if(this.candidateProfileCV[0] && !this.candidateProfileCV[0].approved){
      this._toastr.error('Your CV files is not approved by the administrator');
      error = false;
      if(field === 'looking'){
          this.checkLooking = false;
      }
      else if(field === 'visible'){
          this.checkVisible = false;
      }
    }
    if (!video && this.allowVideo === false) {
      this._toastr.error('You need to upload video');
      error = false;
      if(field === 'looking'){
          this.checkLooking = false;
      }
      else if(field === 'visible'){
          this.checkVisible = false;
      }
    }
    if (video && !video.approved && this.allowVideo === false) {
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
   * @param video
   * @param progress
   */
  public lookingJobToggle(field: string, value: boolean, video, progress) {
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
   * Get candidate dashboard statistic
   * @return {Promise<void>}
   */
  public getCandidateDashboard(): Observable<any> {
    return new Observable(observer => {
      try {

        const data = this._candidateService.getCandidateDashboard().then(response => {
          this.allowVideo = response.allowVideo;
          this.accessSeenJob['allowVideo'] = response.allowVideo;
          this.awaiting = response.application.awaiting;
          this.successful = response.application.successful;
          this.declined = response.application.declined;
          this.unsuccessful = response.application.unsuccessful;
          this.missed = response.application.missed;
          this.view = response.stats.view;
          this.unique = response.stats.unique;
          this.play = response.stats.play;
          this.jobAlertsTotal.number = response.jobAlertsTotal;
          this.interviewRequestTotal.number = response.interviewRequestTotal;
          this.preloaderPage = false;

          if(this.progressBar < 50 || !this.candidateProfileCV || !this.candidateProfileCV[0] || !this.candidateProfileCV[0].approved || (this.allowVideo === false && !this.videoCheck) || (this.allowVideo === false && this.videoCheck && this.videoCheck.approved === false)){
            this.checkVisible = false;
            this.checkLooking = false;
            this.visibilityLooking = true;
            this.visibilityVisible = true;
          }

          response.jobAlerts.forEach((item) => {
            this._mapsAPILoader.load().then(() => {
              const distance = new google.maps.DistanceMatrixService();
              distance.getDistanceMatrix({
                origins: [response.candidateAddress],
                destinations: [item.companyAddress],
                travelMode: google.maps.TravelMode.DRIVING,
              }, callback);

              function callback (response, status) {
                let newDistance;
                newDistance = response;

                if (newDistance) {
                  if(newDistance.rows){
                    if(newDistance.rows[0].elements[0].distance){
                      if(newDistance.rows[0].elements[0].distance.text){
                        const dataDistance = newDistance.rows[0].elements[0].distance.value / 1000;
                        const dataNewDistance = Math.ceil(dataDistance);
                        observer.next(dataNewDistance + ' km');
                      }
                    }
                    else if (newDistance.rows[0].elements[0].status){
                      observer.next('-');
                    }
                  }
                  else {
                    observer.next('-');
                  }
                }
                else {
                  observer.next('-');
                }
              }

              this.jobAlerts.push(item);
            });
          });

          this.sendDataDistance(response).subscribe(data => {
            this.distanceInterviewRequets.push(data);
            this._ref.detectChanges();
          });
        });
      }
      catch (err) {
        this._sharedService.showRequestErrors(err);
      }
    });
  }

  /**
   * Send distance array
   * @param response
   * @returns {Observable|'../../../Observable".Observable|"../../Observable".Observable}
   */
  public sendDataDistance(response): Observable<any> {
    return new Observable(observer => {
      response.interviewRequest.forEach((item) => {
        this._mapsAPILoader.load().then(() => {
          const distance = new google.maps.DistanceMatrixService();
          distance.getDistanceMatrix({
            origins: [response.candidateAddress],
            destinations: [item.companyAddress],
            travelMode: google.maps.TravelMode.DRIVING,
          }, callback);

          function callback (response, status) {
            let newDistance;
            newDistance = response;

            if (newDistance) {
              if(newDistance.rows){
                if(newDistance.rows[0].elements[0].distance){
                  if(newDistance.rows[0].elements[0].distance.text){
                    const dataDistance = newDistance.rows[0].elements[0].distance.value / 1000;
                    const dataNewDistance = Math.ceil(dataDistance);
                    observer.next(dataNewDistance + ' km');
                  }
                }
                else if (newDistance.rows[0].elements[0].status){
                  observer.next('-');
                }
              }
              else {
                observer.next('-');
              }
            }
            else {
              observer.next('-');
            }
          }
          this.interviewRequest.push(item);
        });
      });
    });

  }

  /**
   * opens popup
   * @param content - content to be placed within
   * @param jobArray {array}
   * @param job - job id to show within popup
   * @param status {number}
   */
  public openVerticallyCenter(content: any, jobArray, job, status) {
    this.modalActiveClose = this._modalService.open(content, { centered: true, 'size': 'lg' });
    this.currentJob = job;
    this.jobArray = jobArray;
    this.statusButtonJob = status;
  }

  /**
   * opens popup
   * @param content - content to be placed within
   */
  public openAcceptPopup(content: any) {
    this.modalActiveClose = this._modalService.open(content, { centered: true, windowClass: 'accept-popop', 'size': 'sm' });
  }

  /**
   * opens popup
   * @param content - content to be placed within
   */
  public openAcceptPopups(content: any) {
    this.modalActiveClose = this._modalService.open(content, { centered: true, windowClass: 'accept-popop content-disable', 'size': 'sm' });
  }

}
